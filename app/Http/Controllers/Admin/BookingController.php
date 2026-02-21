<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\GoogleCalendarService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '1month');
        $consultant_id = $request->get('consultant_id');
        $status = $request->get('status');
        $booking_type = $request->get('booking_type', 'all');

        $now = now();
        $endDate = match ($period) {
            '1week' => $now->copy()->addWeek(),
            '2weeks' => $now->copy()->addWeeks(2),
            '1month' => $now->copy()->addMonth(),
            '2months' => $now->copy()->addMonths(2),
            '3months' => $now->copy()->addMonths(3),
            '6months' => $now->copy()->addMonths(6),
            'all' => null,
            default => $now->copy()->addMonth(),
        };

        $query = Booking::with(['user', 'consultant', 'schedule'])
            ->whereIn('status', ['pending', 'approved']);

        if ($endDate) {
            $query->where('booking_date', '>=', $now->toDateString())
                  ->where('booking_date', '<=', $endDate->toDateString());
        } else {
            $query->where('booking_date', '>=', $now->toDateString());
        }

        if ($consultant_id) {
            $query->where('consultant_id', $consultant_id);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by booking type (member / guest)
        if ($booking_type === 'member') {
            $query->where('is_guest', false);
        } elseif ($booking_type === 'guest') {
            $query->where('is_guest', true);
        }

        $bookings = $query->orderBy('booking_date')
            ->orderBy('start_time')
            ->paginate(20)
            ->appends($request->query());

        $totalCount = $query->getQuery()->getCountForPagination();

        $consultants = User::where('role', 'consultant')
            ->orderBy('name')
            ->get();

        $periods = [
            '1week' => '1週間',
            '2weeks' => '2週間',
            '1month' => '1ヶ月',
            '2months' => '2ヶ月',
            '3months' => '3ヶ月',
            '6months' => '6ヶ月',
            'all' => '全期間',
        ];

        $emailTemplates = [
            [
                'label' => '予約確認',
                'subject' => SystemSetting::get('guest_email_tpl_confirm_subject', '【予約確認】個別相談のご予約について'),
                'body' => SystemSetting::get('guest_email_tpl_confirm_body', "{name}様\n\nご予約の確認をお願いいたします。\n\n■ 日時: {date}\n\nご不明な点がございましたらお気軽にご連絡ください。"),
            ],
            [
                'label' => 'リマインド',
                'subject' => SystemSetting::get('guest_email_tpl_remind_subject', '【リマインド】個別相談のご予約について'),
                'body' => SystemSetting::get('guest_email_tpl_remind_body', "{name}様\n\n個別相談の予約日時が近づいてまいりました。\n\n■ 日時: {date}\n\nご準備のほどよろしくお願いいたします。"),
            ],
            [
                'label' => 'フォローアップ',
                'subject' => SystemSetting::get('guest_email_tpl_followup_subject', '【フォローアップ】個別相談について'),
                'body' => SystemSetting::get('guest_email_tpl_followup_body', "{name}様\n\n先日の個別相談はいかがでしたでしょうか。\nご不明な点やご質問がございましたらお気軽にお問い合わせください。"),
            ],
            [
                'label' => 'お知らせ',
                'subject' => SystemSetting::get('guest_email_tpl_notice_subject', '【お知らせ】'),
                'body' => SystemSetting::get('guest_email_tpl_notice_body', "{name}様\n\nお知らせがございます。\n詳細につきましては下記をご確認ください。\n\n"),
            ],
        ];

        return view('admin.bookings.index', compact('bookings', 'consultants', 'periods', 'period', 'consultant_id', 'status', 'booking_type', 'emailTemplates'));
    }

    public function approve(Booking $booking)
    {
        if (!$booking->isPending()) {
            return back()->with('error', 'この予約は承認できません。');
        }

        $booking->update(['status' => 'approved']);

        try {
            $googleService = app(GoogleCalendarService::class);
            $eventId = $googleService->createEvent($booking);
            if ($eventId) {
                $booking->update(['google_event_id' => $eventId]);
            }
        } catch (\Exception $e) {
            // Optional
        }

        AuditLog::log('booking_approved', $booking);

        $notificationService = app(NotificationService::class);
        $notificationService->sendBookingConfirmation($booking);

        return back()->with('success', '予約を承認しました。');
    }

    public function reject(Request $request, Booking $booking)
    {
        if (!$booking->isPending()) {
            return back()->with('error', 'この予約は却下できません。');
        }

        $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update([
            'status' => 'rejected',
            'cancel_reason' => $request->cancel_reason,
        ]);

        AuditLog::log('booking_rejected', $booking);

        $notificationService = app(NotificationService::class);
        $notificationService->sendBookingRejected($booking);

        return back()->with('success', '予約を却下しました。');
    }
}
