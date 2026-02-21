<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\NotificationLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendBookingConfirmation(Booking $booking): void
    {
        if ($booking->isGuest()) {
            $this->sendGuestBookingApproved($booking);
            return;
        }

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

    private function sendGuestBookingApproved(Booking $booking): void
    {
        $consultant = $booking->consultant;
        $date = $booking->booking_date->format('Y年m月d日');
        $time = substr($booking->start_time, 0, 5) . ' - ' . substr($booking->end_time, 0, 5);

        $subject = '【予約確定】個別相談のご予約が確定しました';
        $content = "{$booking->guest_name}様\n\n"
            . "個別相談のご予約が確定しました。\n\n"
            . "■ 日時: {$date} {$time}\n"
            . "■ ステータス: 承認済み\n\n"
            . "よろしくお願いいたします。";

        $this->sendGuestEmail($booking, $subject, $content);
    }

    public function sendBookingCancelled(Booking $booking): void
    {
        $consultant = $booking->consultant;
        $date = $booking->booking_date->format('Y年m月d日');
        $bookerName = $booking->bookerName();

        $subject = '【キャンセル】コンサルティング予約のキャンセル';

        if ($booking->isGuest()) {
            $content = "{$bookerName}様\n\n"
                . "以下の予約がキャンセルされました。\n\n"
                . "■ 日時: {$date}\n"
                . ($booking->cancel_reason ? "■ 理由: {$booking->cancel_reason}\n" : '');

            $this->sendGuestEmail($booking, $subject, $content);
        } else {
            $user = $booking->user;
            $content = "{$user->name}様\n\n"
                . "以下の予約がキャンセルされました。\n\n"
                . "■ コンサルタント: {$consultant->name}\n"
                . "■ 日時: {$date}\n"
                . ($booking->cancel_reason ? "■ 理由: {$booking->cancel_reason}\n" : '');

            $this->send($user, $booking, 'booking_cancelled', $subject, $content);
        }

        // Also notify consultant
        $consultantContent = "{$consultant->name}様\n\n"
            . "{$bookerName}様の以下の予約がキャンセルされました。\n\n"
            . "■ 日時: {$date}\n";

        $this->send($consultant, $booking, 'booking_cancelled', $subject, $consultantContent);
    }

    public function sendBookingRejected(Booking $booking): void
    {
        $consultant = $booking->consultant;
        $date = $booking->booking_date->format('Y年m月d日');

        $subject = '【予約不承認】コンサルティング予約について';

        if ($booking->isGuest()) {
            $content = "{$booking->guest_name}様\n\n"
                . "申し訳ございませんが、以下の予約が承認されませんでした。\n\n"
                . "■ 日時: {$date}\n"
                . ($booking->cancel_reason ? "■ 理由: {$booking->cancel_reason}\n" : '')
                . "\n別の日時をお試しください。";

            $this->sendGuestEmail($booking, $subject, $content);
        } else {
            $user = $booking->user;
            $content = "{$user->name}様\n\n"
                . "申し訳ございませんが、以下の予約が承認されませんでした。\n\n"
                . "■ コンサルタント: {$consultant->name}\n"
                . "■ 日時: {$date}\n"
                . ($booking->cancel_reason ? "■ 理由: {$booking->cancel_reason}\n" : '')
                . "\n別の日時をお試しください。";

            $this->send($user, $booking, 'booking_rejected', $subject, $content);
        }
    }

    public function sendGuestBookingConfirmation(Booking $booking): void
    {
        $date = $booking->booking_date->format('Y年m月d日');
        $time = substr($booking->start_time, 0, 5) . ' - ' . substr($booking->end_time, 0, 5);

        $customMessage = SystemSetting::get('guest_booking_confirmation_message', '');

        $subject = '【予約受付】個別相談のご予約を承りました';
        $content = "{$booking->guest_name}様\n\n"
            . "個別相談のご予約を受け付けました。\n"
            . "担当者が確認後、改めてご連絡いたします。\n\n"
            . "■ 日時: {$date} {$time}\n"
            . "■ ステータス: 確認待ち\n";

        if ($customMessage) {
            $content .= "\n{$customMessage}\n";
        }

        $content .= "\nよろしくお願いいたします。";

        $this->sendGuestEmail($booking, $subject, $content);

        // Also notify consultant
        $consultant = $booking->consultant;
        $consultantContent = "{$consultant->name}様\n\n"
            . "個別相談の新しい予約が入りました。\n\n"
            . "■ お客様名: {$booking->guest_name}\n"
            . "■ メール: {$booking->guest_email}\n"
            . "■ 電話番号: {$booking->guest_phone}\n"
            . "■ 日時: {$date} {$time}\n"
            . ($booking->notes ? "■ 相談内容: {$booking->notes}\n" : '');

        $this->send($consultant, $booking, 'booking_confirmed', $subject, $consultantContent);
    }

    public function sendGuestReminder(Booking $booking, string $type): void
    {
        $date = $booking->booking_date->format('Y年m月d日');
        $time = substr($booking->start_time, 0, 5) . ' - ' . substr($booking->end_time, 0, 5);

        $minutesBefore = (int) SystemSetting::get('reminder_minutes_before', 10);
        $typeLabel = match ($type) {
            'reminder_day_before' => '明日',
            'reminder_day_of' => '本日',
            'reminder_before_start' => "{$minutesBefore}分後",
            default => '',
        };

        $consultant = $booking->consultant;
        $consultantProfile = $consultant->consultantProfile;
        $meetingUrl = $booking->meeting_url ?: ($consultantProfile?->meeting_url ?? null);

        $customMessage = $consultantProfile?->reminder_message
            ?: SystemSetting::get('guest_reminder_message')
            ?: 'お忘れなくご参加ください。';

        $subject = "【リマインド】{$typeLabel}の個別相談のご予約";
        $content = "{$booking->guest_name}様\n\n"
            . "{$typeLabel}、個別相談のご予約があります。\n\n"
            . "■ 日時: {$date} {$time}\n"
            . ($meetingUrl ? "■ ミーティングURL: {$meetingUrl}\n" : '')
            . "\n{$customMessage}";

        $this->sendGuestEmail($booking, $subject, $content);

        // Also remind consultant
        $consultantContent = "{$consultant->name}様\n\n"
            . "{$typeLabel}、{$booking->guest_name}様（個別相談）との予約があります。\n\n"
            . "■ 日時: {$date} {$time}\n"
            . "■ 電話番号: {$booking->guest_phone}\n"
            . ($meetingUrl ? "■ ミーティングURL: {$meetingUrl}\n" : '');

        $this->send($consultant, $booking, $type, $subject, $consultantContent);
    }

    public function sendReminder(Booking $booking, string $type): void
    {
        // Dispatch to guest reminder if this is a guest booking
        if ($booking->isGuest()) {
            $this->sendGuestReminder($booking, $type);
            return;
        }

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
        if ($user->notify_email) {
            $this->sendEmail($user, $booking, $type, $subject, $content);
        }

        if ($user->notify_line) {
            $this->sendLine($user, $booking, $type, $content);
        }

        // Always send to Chatwork if room ID is configured
        if ($user->chatwork_room_id) {
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

            $this->logNotification($user->id, $booking->id, 'email', $type, $subject, $content, 'sent');
        } catch (\Exception $e) {
            $this->logNotification($user->id, $booking->id, 'email', $type, $subject, $content, 'failed', $e->getMessage());
        }
    }

    private function sendGuestEmail(Booking $booking, string $subject, string $content): void
    {
        try {
            Mail::raw($content, function ($message) use ($booking, $subject) {
                $message->to($booking->guest_email)
                    ->subject($subject);
            });

            $this->logNotification(null, $booking->id, 'email', 'booking_confirmed', $subject, $content, 'sent');
        } catch (\Exception $e) {
            $this->logNotification(null, $booking->id, 'email', 'booking_confirmed', $subject, $content, 'failed', $e->getMessage());
        }
    }

    private function sendChatwork($user, Booking $booking, string $type, string $content): void
    {
        try {
            $chatworkService = new ChatworkService();
            $message = $user->chatwork_id
                ? "[To:{$user->chatwork_id}]{$user->name}さん\n{$content}"
                : "{$user->name}さん\n{$content}";
            $chatworkService->sendMessage($user->chatwork_room_id, $message);

            $this->logNotification($user->id, $booking->id, 'chatwork', $type, null, $content, 'sent');
        } catch (\Exception $e) {
            $this->logNotification($user->id, $booking->id, 'chatwork', $type, null, $content, 'failed', $e->getMessage());
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

            $this->logNotification($user->id, $booking->id, 'line', $type, null, $content, 'sent');
        } catch (\Exception $e) {
            $this->logNotification($user->id, $booking->id, 'line', $type, null, $content, 'failed', $e->getMessage());
        }
    }

    private function logNotification(?int $userId, int $bookingId, string $channel, string $type, ?string $subject, ?string $content, string $status, ?string $errorMessage = null): void
    {
        try {
            NotificationLog::create([
                'user_id' => $userId,
                'booking_id' => $bookingId,
                'channel' => $channel,
                'type' => $type,
                'subject' => $subject,
                'content' => $content,
                'status' => $status,
                'error_message' => $errorMessage,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to write notification log', [
                'booking_id' => $bookingId,
                'channel' => $channel,
                'type' => $type,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
