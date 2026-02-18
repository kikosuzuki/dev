<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;

class LineNotificationService
{
    private string $channelToken;

    public function __construct()
    {
        $this->channelToken = SystemSetting::get('line_channel_token', config('services.line.channel_token', ''));
    }

    public function pushMessage(string $userId, string $message): bool
    {
        if (empty($this->channelToken)) {
            throw new \RuntimeException('LINE Channel Token is not configured.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->channelToken,
            'Content-Type' => 'application/json',
        ])->post('https://api.line.me/v2/bot/message/push', [
            'to' => $userId,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message,
                ],
            ],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('LINE API error: ' . $response->body());
        }

        return true;
    }
}
