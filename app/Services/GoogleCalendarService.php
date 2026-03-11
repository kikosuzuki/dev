<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ConsultantSchedule;
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
    // Calendar conflict checking methods
    // ========================================

    /**
     * List all calendars including read-only ones (for conflict checking).
     */
    public function listAllCalendars(string $refreshToken): array
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
            $calendars[] = [
                'id' => $item['id'],
                'summary' => $item['summary'] ?? $item['id'],
                'primary' => $item['primary'] ?? false,
                'accessRole' => $item['accessRole'] ?? 'reader',
            ];
        }

        return $calendars;
    }

    /**
     * List events from a single calendar within a date range.
     */
    public function listEvents(string $refreshToken, string $calendarId, string $timeMin, string $timeMax): array
    {
        $accessToken = $this->getAccessTokenFromRefreshToken($refreshToken);
        if (!$accessToken) {
            return [];
        }

        $events = [];
        $pageToken = null;

        do {
            $params = [
                'timeMin' => $timeMin,
                'timeMax' => $timeMax,
                'singleEvents' => 'true',
                'orderBy' => 'startTime',
                'timeZone' => 'Asia/Tokyo',
                'maxResults' => 2500,
                'fields' => 'items(id,summary,start,end,status),nextPageToken',
            ];

            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get("https://www.googleapis.com/calendar/v3/calendars/" . urlencode($calendarId) . "/events", $params);

            if (!$response->successful()) {
                Log::warning('Failed to list calendar events', [
                    'calendar_id' => $calendarId,
                    'status' => $response->status(),
                ]);
                break;
            }

            foreach ($response->json('items', []) as $item) {
                if (($item['status'] ?? '') === 'cancelled') {
                    continue;
                }

                $start = $item['start']['dateTime'] ?? null;
                $end = $item['end']['dateTime'] ?? null;

                // Handle all-day events
                if (!$start && isset($item['start']['date'])) {
                    $start = $item['start']['date'] . 'T00:00:00+09:00';
                    $end = $item['end']['date'] . 'T00:00:00+09:00';
                }

                if ($start && $end) {
                    $events[] = [
                        'summary' => $item['summary'] ?? '(予定)',
                        'start' => $start,
                        'end' => $end,
                    ];
                }
            }

            $pageToken = $response->json('nextPageToken');
        } while ($pageToken);

        return $events;
    }

    /**
     * List events from multiple calendars and merge results.
     */
    public function listEventsFromMultipleCalendars(string $refreshToken, array $calendarIds, string $timeMin, string $timeMax): array
    {
        $allEvents = [];

        foreach ($calendarIds as $calendarId) {
            try {
                $events = $this->listEvents($refreshToken, $calendarId, $timeMin, $timeMax);
                $allEvents = array_merge($allEvents, $events);
            } catch (\Exception $e) {
                Log::warning('Failed to fetch events from calendar', [
                    'calendar_id' => $calendarId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $allEvents;
    }

    /**
     * Check proposed slots against calendar events for conflicts.
     * Each slot: ['date' => 'Y-m-d', 'start_time' => 'H:i:s', 'end_time' => 'H:i:s']
     * Returns slots with has_conflict and conflicting_events added.
     */
    public function findConflicts(array $events, array $proposedSlots): array
    {
        $result = [];

        foreach ($proposedSlots as $slot) {
            $slotStart = \Carbon\Carbon::parse($slot['date'] . ' ' . $slot['start_time'], 'Asia/Tokyo');
            $slotEnd = \Carbon\Carbon::parse($slot['date'] . ' ' . $slot['end_time'], 'Asia/Tokyo');

            $conflicts = [];
            foreach ($events as $event) {
                $eventStart = \Carbon\Carbon::parse($event['start'])->setTimezone('Asia/Tokyo');
                $eventEnd = \Carbon\Carbon::parse($event['end'])->setTimezone('Asia/Tokyo');

                if ($eventStart->lt($slotEnd) && $eventEnd->gt($slotStart)) {
                    $conflicts[] = [
                        'summary' => $event['summary'],
                        'start' => $eventStart->format('H:i'),
                        'end' => $eventEnd->format('H:i'),
                    ];
                }
            }

            $slot['has_conflict'] = !empty($conflicts);
            $slot['conflicting_events'] = $conflicts;
            $result[] = $slot;
        }

        return $result;
    }

    /**
     * Check a single slot for conflicts (used at booking time).
     * Returns conflicting event summary or null.
     */
    public function checkSlotConflict(string $refreshToken, array $calendarIds, string $date, string $startTime, string $endTime): ?string
    {
        if (empty($calendarIds)) {
            return null;
        }

        try {
            $timeMin = $date . 'T00:00:00+09:00';
            $timeMax = $date . 'T23:59:59+09:00';

            $events = $this->listEventsFromMultipleCalendars($refreshToken, $calendarIds, $timeMin, $timeMax);

            $slots = [['date' => $date, 'start_time' => $startTime, 'end_time' => $endTime]];
            $result = $this->findConflicts($events, $slots);

            if (!empty($result[0]['has_conflict'])) {
                return $result[0]['conflicting_events'][0]['summary'] ?? '(予定)';
            }
        } catch (\Exception $e) {
            Log::warning('Failed to check slot conflict', [
                'date' => $date,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    // ========================================
    // Available slot sync methods
    // ========================================

    /**
     * Create a transparent (Free) event on consultant's calendar for an available slot.
     * Returns the created event ID or null.
     */
    public function createAvailableSlotEvent(ConsultantSchedule $schedule): ?string
    {
        $consultant = $schedule->consultant;
        $profile = $consultant->consultantProfile;

        if (!$profile || !$profile->isGoogleConnected() || !$profile->sync_available_slots) {
            return null;
        }

        $accessToken = $this->getAccessTokenFromRefreshToken($profile->google_refresh_token);
        if (!$accessToken) {
            return null;
        }

        $calendarId = $profile->google_calendar_id ?: 'primary';
        $startTime = substr($schedule->start_time, 0, 5);
        $endTime = substr($schedule->end_time, 0, 5);

        $event = [
            'summary' => "【空き枠】{$startTime}〜{$endTime}",
            'start' => [
                'dateTime' => $schedule->date->format('Y-m-d') . 'T' . $schedule->start_time,
                'timeZone' => 'Asia/Tokyo',
            ],
            'end' => [
                'dateTime' => $schedule->date->format('Y-m-d') . 'T' . $schedule->end_time,
                'timeZone' => 'Asia/Tokyo',
            ],
            'transparency' => 'transparent',
            'description' => "予約システムの空き枠です。\n予約が入ると自動的に削除されます。",
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post("https://www.googleapis.com/calendar/v3/calendars/" . urlencode($calendarId) . "/events", $event);

        if ($response->successful()) {
            $eventId = $response->json('id');
            $schedule->update(['google_event_id' => $eventId]);
            return $eventId;
        }

        Log::warning('Failed to create available slot event', [
            'schedule_id' => $schedule->id,
            'status' => $response->status(),
        ]);

        return null;
    }

    /**
     * Delete the available slot event from consultant's calendar.
     */
    public function deleteAvailableSlotEvent(ConsultantSchedule $schedule): void
    {
        if (!$schedule->google_event_id) {
            return;
        }

        $consultant = $schedule->consultant;
        $profile = $consultant->consultantProfile;

        if (!$profile || !$profile->isGoogleConnected()) {
            $schedule->update(['google_event_id' => null]);
            return;
        }

        $calendarId = $profile->google_calendar_id ?: 'primary';

        try {
            $this->deleteEventForConsultant(
                $schedule->google_event_id,
                $profile->google_refresh_token,
                $calendarId
            );
        } catch (\Exception $e) {
            Log::warning('Failed to delete available slot event', [
                'schedule_id' => $schedule->id,
                'event_id' => $schedule->google_event_id,
                'error' => $e->getMessage(),
            ]);
        }

        $schedule->update(['google_event_id' => null]);
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
