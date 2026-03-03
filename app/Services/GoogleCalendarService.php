<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    // ========================================
    // Admin calendar methods (existing behavior)
    // ========================================

    public function createEvent(Booking $booking, bool $excludeConsultantFromAttendees = false): ?string
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $event = $this->buildEventPayload($booking, $excludeConsultantFromAttendees);
        $calendarId = SystemSetting::get('google_calendar_id', 'primary');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post("https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events", $event);

        if ($response->successful()) {
            return $response->json('id');
        }

        return null;
    }

    public function deleteEvent(string $eventId): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        $calendarId = SystemSetting::get('google_calendar_id', 'primary');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->delete("https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events/{$eventId}");

        return $response->successful();
    }

    // ========================================
    // Consultant calendar methods (new)
    // ========================================

    /**
     * Create event on a consultant's own Google Calendar.
     */
    public function createEventForConsultant(Booking $booking, string $refreshToken, string $calendarId = 'primary'): ?string
    {
        $accessToken = $this->getAccessTokenFromRefreshToken($refreshToken);
        if (!$accessToken) {
            return null;
        }

        $event = $this->buildEventPayload($booking);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post("https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events", $event);

        if ($response->successful()) {
            return $response->json('id');
        }

        Log::warning('Failed to create consultant calendar event', [
            'booking_id' => $booking->id,
            'status' => $response->status(),
        ]);

        return null;
    }

    /**
     * Delete event from a consultant's own Google Calendar.
     */
    public function deleteEventForConsultant(string $eventId, string $refreshToken, string $calendarId = 'primary'): bool
    {
        $accessToken = $this->getAccessTokenFromRefreshToken($refreshToken);
        if (!$accessToken) {
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->delete("https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events/{$eventId}");

        return $response->successful();
    }

    /**
     * List calendars for a given refresh token.
     */
    public function listCalendars(string $refreshToken): array
    {
        $accessToken = $this->getAccessTokenFromRefreshToken($refreshToken);
        if (!$accessToken) {
            return [];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get('https://www.googleapis.com/calendar/v3/users/me/calendarList');

        if (!$response->successful()) {
            return [];
        }

        $calendars = [];
        foreach ($response->json('items', []) as $item) {
            if (($item['accessRole'] ?? '') === 'owner' || ($item['accessRole'] ?? '') === 'writer') {
                $calendars[] = [
                    'id' => $item['id'],
                    'summary' => $item['summary'] ?? $item['id'],
                    'primary' => $item['primary'] ?? false,
                ];
            }
        }

        return $calendars;
    }

    // ========================================
    // Sync methods (admin + consultant)
    // ========================================

    /**
     * Create event on both admin and consultant calendars.
     * Returns [$adminEventId, $consultantEventId].
     */
    public function syncCreateEvent(Booking $booking): array
    {
        $adminEventId = null;
        $consultantEventId = null;

        // Check if consultant has their own Google Calendar connected
        $consultant = $booking->consultant;
        $profile = $consultant->consultantProfile;
        $consultantHasOwnCalendar = $profile && $profile->isGoogleConnected();

        // 1. Admin calendar
        // If consultant has own calendar, exclude them from attendees to avoid duplicate
        try {
            $adminEventId = $this->createEvent($booking, $consultantHasOwnCalendar);
        } catch (\Exception $e) {
            // Admin calendar is optional
        }

        // 2. Consultant's own calendar
        try {
            if ($consultantHasOwnCalendar) {
                $calendarId = $profile->google_calendar_id ?: 'primary';
                $consultantEventId = $this->createEventForConsultant(
                    $booking,
                    $profile->google_refresh_token,
                    $calendarId
                );
            }
        } catch (\Exception $e) {
            // Consultant calendar is optional
        }

        return [$adminEventId, $consultantEventId];
    }

    /**
     * Delete event from both admin and consultant calendars.
     */
    public function syncDeleteEvent(Booking $booking): void
    {
        // 1. Admin calendar
        if ($booking->google_event_id) {
            try {
                $this->deleteEvent($booking->google_event_id);
            } catch (\Exception $e) {
                // Ignore
            }
        }

        // 2. Consultant calendar
        if ($booking->consultant_google_event_id) {
            try {
                $consultant = $booking->consultant;
                $profile = $consultant->consultantProfile;
                if ($profile && $profile->isGoogleConnected()) {
                    $calendarId = $profile->google_calendar_id ?: 'primary';
                    $this->deleteEventForConsultant(
                        $booking->consultant_google_event_id,
                        $profile->google_refresh_token,
                        $calendarId
                    );
                }
            } catch (\Exception $e) {
                // Ignore
            }
        }
    }

    // ========================================
    // Private helpers
    // ========================================

    private function buildEventPayload(Booking $booking, bool $excludeConsultantFromAttendees = false): array
    {
        $consultant = $booking->consultant;
        $bookerName = $booking->bookerName();
        $bookerEmail = $booking->bookerEmail();
        $startDateTime = $booking->booking_date->format('Y-m-d') . 'T' . $booking->start_time;
        $endDateTime = $booking->booking_date->format('Y-m-d') . 'T' . $booking->end_time;

        $attendees = [];
        if ($bookerEmail) {
            $attendees[] = ['email' => $bookerEmail];
        }
        if (!$excludeConsultantFromAttendees) {
            $attendees[] = ['email' => $consultant->email];
        }

        return [
            'summary' => "コンサルティング: {$bookerName}様 × {$consultant->name}",
            'description' => "予約ID: {$booking->id}\n"
                . "ユーザー: {$bookerName} ({$bookerEmail})\n"
                . "コンサルタント: {$consultant->name}\n"
                . ($booking->notes ? "メモ: {$booking->notes}" : ''),
            'start' => [
                'dateTime' => $startDateTime,
                'timeZone' => 'Asia/Tokyo',
            ],
            'end' => [
                'dateTime' => $endDateTime,
                'timeZone' => 'Asia/Tokyo',
            ],
            'attendees' => $attendees,
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 1440],
                    ['method' => 'popup', 'minutes' => 10],
                ],
            ],
        ];
    }

    private function isEnabled(): bool
    {
        return (bool) SystemSetting::get('google_calendar_enabled', false);
    }

    private function getAccessToken(): ?string
    {
        $refreshToken = SystemSetting::get('google_refresh_token');
        return $this->getAccessTokenFromRefreshToken($refreshToken);
    }

    public function getAccessTokenFromRefreshToken(?string $refreshToken): ?string
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (!$refreshToken || !$clientId || !$clientSecret) {
            return null;
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        return null;
    }
}
