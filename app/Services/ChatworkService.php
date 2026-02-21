<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatworkService
{
    private ?string $apiToken;

    public function __construct()
    {
        $this->apiToken = SystemSetting::get('chatwork_api_token');
    }

    public function getRoomMembers(string $roomId): array
    {
        if (!$this->apiToken) {
            Log::warning('Chatwork API token is not configured.');
            return [];
        }

        try {
            $response = Http::withHeaders([
                'X-ChatWorkToken' => $this->apiToken,
            ])->get("https://api.chatwork.com/v2/rooms/{$roomId}/members");

            if ($response->successful()) {
                return collect($response->json())->map(fn ($member) => [
                    'account_id' => $member['account_id'],
                    'name' => $member['name'],
                ])->toArray();
            }

            Log::error('Chatwork API error fetching members', [
                'room_id' => $roomId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('Chatwork get room members failed', [
                'room_id' => $roomId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function sendMessage(string $roomId, string $message): bool
    {
        if (!$this->apiToken) {
            Log::warning('Chatwork API token is not configured.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'X-ChatWorkToken' => $this->apiToken,
            ])->asForm()->post("https://api.chatwork.com/v2/rooms/{$roomId}/messages", [
                'body' => $message,
                'self_unread' => 1,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Chatwork API error', [
                'room_id' => $roomId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Chatwork send message failed', [
                'room_id' => $roomId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
