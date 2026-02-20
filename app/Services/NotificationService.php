<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\NotificationLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendBookingConfirmation(Booking $booking): void
    {
        $user = $booking->user;
        $consultant = $booking->consultant;
        $date = $booking->booking_date->format('Y年m月d日');
        $time = substr($booking->start_time, 0, 5) . ' - ' . substr($booking->end_time, 0, 5);

        $subject = '【予約確定】コンサルティング予約のお知らせ';
        $content = "{$user->name}様\n\n"
            . "コンサルティングの予約が確定しました。\n\n"
            . "■ コンサルタント: {$consultant->name}\n"
            . "■ 日時: {$date} {$time}\n"
            . "■ ステータス: {$booking->status}\n\n"
            . "よろしくお願いいたします。";

        $this->send($user, $booking, 'booking_confirmed', $subject, $content);
    }

    public function sendBookingCancelled(Booking $booking): void
    {
        $user = $booking->user;
        $consultant = $booking->consultant;
        $date = $booking->booking_date->format('Y年m月d日');

        $subject = '【キャンセル】コンサルティング予約のキャンセル';
        $content = "{$user->name}様\n\n"
            . "以下の予約がキャンセルされました。\n\n"
            . "■ コンサルタント: {$consultant->name}\n"
            . "■ 日時: {$date}\n"
            . ($booking->cancel_reason ? "■ 理由: {$booking->cancel_reason}\n" : '');

        $this->send($user, $booking, 'booking_cancelled', $subject, $content);

        // Also notify consultant
        $consultantContent = "{$consultant->name}様\n\n"
            . "{$user->name}様の以下の予約がキャンセルされました。\n\n"
            . "■ 日時: {$date}\n";

        $this->send($consultant, $booking, 'booking_cancelled', $subject, $consultantContent);
    }

    public function sendBookingRejected(Booking $booking): void
    {
        $user = $booking->user;
        $consultant = $booking->consultant;
        $date = $booking->booking_date->format('Y年m月d日');

        $subject = '【予約不承認】コンサルティング予約について';
        $content = "{$user->name}様\n\n"
            . "申し訳ございませんが、以下の予約が承認されませんでした。\n\n"
            . "■ コンサルタント: {$consultant->name}\n"
            . "■ 日時: {$date}\n"
            . ($booking->cancel_reason ? "■ 理由: {$booking->cancel_reason}\n" : '')
            . "\n別の日時をお試しください。";

        $this->send($user, $booking, 'booking_rejected', $subject, $content);
    }

    public function sendReminder(Booking $booking, string $type): void
    {
        $user = $booking->user;
        $consultant = $booking->consultant;
        $consultantProfile = $consultant->consultantProfile;
        $date = $booking->booking_date->format('Y年m月d日');
        $time = substr($booking->start_time, 0, 5) . ' - ' . substr($booking->end_time, 0, 5);

        $minutesBefore = (int) SystemSetting::get('reminder_minutes_before', 10);
        $typeLabel = match ($type) {
            'reminder_day_before' => '明日',
            'reminder_day_of' => '本日',
            'reminder_before_start' => "{$minutesBefore}分後",
            default => '',
        };

        // Determine meeting URL: booking > consultant profile
        $meetingUrl = $booking->meeting_url ?: ($consultantProfile?->meeting_url ?? null);

        // Determine custom message: consultant profile > system default > built-in
        $customMessage = $consultantProfile?->reminder_message
            ?: SystemSetting::get('default_reminder_message')
            ?: 'お忘れなくご参加ください。';

        $subject = "【リマインド】{$typeLabel}のコンサルティング予約";
        $content = "{$user->name}様\n\n"
            . "{$typeLabel}、コンサルティングの予約があります。\n\n"
            . "■ コンサルタント: {$consultant->name}\n"
            . "■ 日時: {$date} {$time}\n"
            . ($meetingUrl ? "■ ミーティングURL: {$meetingUrl}\n" : '')
            . "\n{$customMessage}";

        $this->send($user, $booking, $type, $subject, $content);

        // Also remind consultant
        $consultantContent = "{$consultant->name}様\n\n"
            . "{$typeLabel}、{$user->name}様とのコンサルティングがあります。\n\n"
            . "■ 日時: {$date} {$time}\n"
            . ($meetingUrl ? "■ ミーティングURL: {$meetingUrl}\n" : '');

        $this->send($consultant, $booking, $type, $subject, $consultantContent);
    }

    private function send($user, Booking $booking, string $type, string $subject, string $content): void
    {
        $channel = $user->notification_channel;

        if (in_array($channel, ['email', 'both'])) {
            $this->sendEmail($user, $booking, $type, $subject, $content);
        }

        if (in_array($channel, ['line', 'both'])) {
            $this->sendLine($user, $booking, $type, $content);
        }

        // Always send to Chatwork if configured
        if ($user->chatwork_id && $user->chatwork_room_id) {
            $this->sendChatwork($user, $booking, $type, $content);
        }
    }

    private function sendEmail($user, Booking $booking, string $type, string $subject, string $content): void
    {
        try {
            Mail::raw($content, function ($message) use ($user, $subject) {
                $message->to($user->email)
                    ->subject($subject);
            });

            NotificationLog::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'channel' => 'email',
                'type' => $type,
                'subject' => $subject,
                'content' => $content,
                'status' => 'sent',
            ]);
        } catch (\Exception $e) {
            NotificationLog::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'channel' => 'email',
                'type' => $type,
                'subject' => $subject,
                'content' => $content,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function sendChatwork($user, Booking $booking, string $type, string $content): void
    {
        try {
            $chatworkService = new ChatworkService();
            $message = "[To:{$user->chatwork_id}]{$user->name}さん\n{$content}";
            $chatworkService->sendMessage($user->chatwork_room_id, $message);

            NotificationLog::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'channel' => 'chatwork',
                'type' => $type,
                'content' => $content,
                'status' => 'sent',
            ]);
        } catch (\Exception $e) {
            NotificationLog::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'channel' => 'chatwork',
                'type' => $type,
                'content' => $content,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function sendLine($user, Booking $booking, string $type, string $content): void
    {
        if (!$user->line_user_id) {
            return;
        }

        try {
            $lineService = app(LineNotificationService::class);
            $lineService->pushMessage($user->line_user_id, $content);

            NotificationLog::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'channel' => 'line',
                'type' => $type,
                'content' => $content,
                'status' => 'sent',
            ]);
        } catch (\Exception $e) {
            NotificationLog::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'channel' => 'line',
                'type' => $type,
                'content' => $content,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
