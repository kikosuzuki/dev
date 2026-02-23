<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Services\GoogleCalendarService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BookingManageController extends Controller
{
    public function index(Request $request)
    {
        $consultant = auth()->user();
        $status = $request->get('status', 'all');

        $query = Booking::where('consultant_id', $consultant->id)
            ->with('user')
            ->orderByDesc('booking_date');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $bookings = $query->paginate(10);

        return view('consultant.bookings.index', compact('bookings', 'status'));
    }

    public function complete(Booking $booking)
    {
        if ($booking->consultant_id !== auth()->id()) {
            abort(403);
        }

        if (!$booking->isApproved()) {
            return back()->with('error', 'この予約は完了にできません。');
        }

        $booking->update(['status' => 'completed']);

        $profile = auth()->user()->consultantProfile;
        if ($profile) {
            $profile->increment('total_bookings');
        }

        AuditLog::log('booking_completed', $booking);

        return back()->with('success', '予約を完了にしました。');
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->consultant_id !== auth()->id()) {
            abort(403);
        }

        if (!$booking->isApproved()) {
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

        AuditLog::log('booking_cancelled_by_consultant', $booking);

        $notificationService = app(NotificationService::class);
        $notificationService->sendBookingCancelled($booking);

        return back()->with('success', '予約をキャンセルしました。予約者に通知が送信されました。');
    }

    public function updateConsultationRecord(Request $request, Booking $booking)
    {
        if ($booking->consultant_id !== auth()->id()) {
            abort(403);
        }

        if (!$booking->isGuest()) {
            return back()->with('error', '個別相談の予約のみ記録できます。');
        }

        $request->validate([
            'consultation_result' => ['required', 'in:success,failure,pending'],
            'consultation_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking->update([
            'consultation_result' => $request->consultation_result,
            'consultation_notes' => $request->consultation_notes,
        ]);

        AuditLog::log('consultation_record_updated', $booking);

        return back()->with('success', '相談記録を保存しました。');
    }
}
