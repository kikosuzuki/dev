<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class GuestEmailController extends Controller
{
    public function send(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        if (!$booking->isGuest()) {
            return back()->with('error', 'この予約はゲスト予約ではありません。');
        }

        if (!$booking->guest_email) {
            return back()->with('error', 'この予約にはゲストのメールアドレスが設定されていません。');
        }

        $dateLabel = $booking->booking_date->format('Y年m月d日') . ' '
            . \Carbon\Carbon::parse($booking->start_time)->format('H:i') . ' - '
            . \Carbon\Carbon::parse($booking->end_time)->format('H:i');

        $replacements = ['{name}' => $booking->guest_name ?? '', '{date}' => $dateLabel];
        $subject = str_replace(array_keys($replacements), array_values($replacements), $validated['subject']);
        $message = str_replace(array_keys($replacements), array_values($replacements), $validated['message']);

        try {
            Mail::raw($message, function ($mail) use ($booking, $subject) {
                $mail->to($booking->guest_email)
                    ->subject($subject);
            });

            return back()->with('success', "{$booking->guest_name}さんにメールを送信しました。");
        } catch (\Exception $e) {
            return back()->with('error', 'メールの送信に失敗しました。メール設定を確認してください。');
        }
    }
}
