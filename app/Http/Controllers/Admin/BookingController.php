<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\GuestMessageTemplate;
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

        $query = Booking::with(['user', 'consultant', 'schedule']);

        // Status filter (default: approved)
        if (!$status || $status === 'approved') {
            $query->where('status', 'approved');
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        } elseif ($status === 'cancelled') {
            $query->where('status', 'cancelled');
        } else {
            // 'all' - no status filter
            $query->whereIn('status', ['approved', 'completed', 'cancelled']);
        }

        if ($endDate) {
            $query->where('booking_date', '>=', $now->toDateString())
                  ->where('booking_date', '<=', $endDate->toDateString());
        } else {
            $query->where('booking_date', '>=', $now->toDateString());
        }

        if ($consultant_id) {
            $query->where('consultant_id', $consultant_id);
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

        $emailTemplates = GuestMessageTemplate::ordered()->get()
            ->map(fn ($t) => [
                'label' => $t->name,
                'subject' => $t->subject ?? '',
                'body' => $t->body ?? '',
            ])->toArray();

        return view('admin.bookings.index', compact('bookings', 'consultants', 'periods', 'period', 'consultant_id', 'status', 'booking_type', 'emailTemplates'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        if (!$booking->canCancel()) {
            return back()->with('error', 'この予約はキャンセルできません。');
        }

        $request->validate([
            'cancel_reason' => ['required', 'string', 'max:500'],
        ]);

        $booking->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->cancel_reason,
        ]);

        if ($booking->google_event_id || $booking->consultant_google_event_id) {
            try {
                $googleService = app(GoogleCalendarService::class);
                $googleService->syncDeleteEvent($booking);
                $booking->update(['google_event_id' => null, 'consultant_google_event_id' => null]);
            } catch (\Exception $e) {
                // Ignore Google Calendar errors
            }
        }

        AuditLog::log('booking_cancelled_by_admin', $booking);

        $notificationService = app(NotificationService::class);
        $notificationService->sendBookingCancelled($booking);

        return back()->with('success', '予約をキャンセルしました。予約者に通知が送信されました。');
    }

    public function updateConsultationRecord(Request $request, Booking $booking)
    {
        $request->validate([
            'consultation_result' => ['required', 'in:success,failure,pending'],
            'consultation_notes' => ['required', 'string'],
        ]);

        $data = [
            'consultation_result' => $request->consultation_result,
            'consultation_notes' => $request->consultation_notes,
        ];

        // 承認済みの予約は自動的に完了にする
        if ($booking->isApproved()) {
            $data['status'] = 'completed';
        }

        $booking->update($data);

        // 完了時にコンサルタントの実績をカウント
        if ($booking->status === 'completed' && $booking->wasChanged('status')) {
            $profile = $booking->consultant?->consultantProfile;
            if ($profile) {
                $profile->increment('total_bookings');
            }
            AuditLog::log('booking_completed', $booking);
        }

        AuditLog::log('consultation_record_updated', $booking);

        return back()->with('success', '相談記録を保存しました。');
    }

    public function updateNotes(Request $request, Booking $booking)
    {
        $request->validate([
            'admin_notes' => ['nullable', 'string'],
        ]);

        $booking->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'メモを保存しました。');
    }
}
