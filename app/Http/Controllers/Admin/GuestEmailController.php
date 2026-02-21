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

        try {
            Mail::raw($validated['message'], function ($mail) use ($booking, $validated) {
                $mail->to($booking->guest_email)
                    ->subject($validated['subject']);
            });

            return back()->with('success', "{$booking->guest_name}さんにメールを送信しました。");
        } catch (\Exception $e) {
            return back()->with('error', 'メールの送信に失敗しました。メール設定を確認してください。');
        }
    }
}
