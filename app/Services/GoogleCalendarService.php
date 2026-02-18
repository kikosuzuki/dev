<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;

class GoogleCalendarService
{
    public function createEvent(Booking $booking): ?string
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $consultant = $booking->consultant;
        $user = $booking->user;
        $startDateTime = $booking->booking_date->format('Y-m-d') . 'T' . $booking->start_time;
        $endDateTime = $booking->booking_date->format('Y-m-d') . 'T' . $booking->end_time;

        $event = [
            'summary' => "コンサルティング: {$user->name}様 × {$consultant->name}",
            'description' => "予約ID: {$booking->id}\n"
                . "ユーザー: {$user->name} ({$user->email})\n"
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
            'attendees' => [
                ['email' => $user->email],
                ['email' => $consultant->email],
            ],
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 1440],
                    ['method' => 'popup', 'minutes' => 10],
                ],
            ],
        ];

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

    private function isEnabled(): bool
    {
        return (bool) SystemSetting::get('google_calendar_enabled', false);
    }

    private function getAccessToken(): ?string
    {
        $refreshToken = SystemSetting::get('google_refresh_token');
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
