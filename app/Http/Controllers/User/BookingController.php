<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\ConsultantSchedule;
use App\Models\SystemSetting;
use App\Services\GoogleCalendarService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $status = $request->get('status', 'all');

        $query = Booking::where('user_id', $user->id)
            ->with(['consultant.consultantProfile', 'schedule'])
            ->orderByDesc('booking_date');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $bookings = $query->paginate(10);

        return view('user.bookings.index', compact('bookings', 'status'));
    }

    public function create(ConsultantSchedule $schedule)
    {
        if (!$schedule->is_available || $schedule->calendar_blocked || $schedule->isBooked()) {
            return back()->with('error', 'この時間枠は既に予約済みまたはブロックされています。');
        }

        $schedule->load('consultant.consultantProfile');

        // コンサルタントの予約受付チェック
        $consultantProfile = $schedule->consultant->consultantProfile;
        if ($consultantProfile && !$consultantProfile->booking_acceptance_enabled) {
            return back()->with('error', 'このコンサルタントは現在予約を受け付けておりません。');
        }

        return view('user.bookings.create', compact('schedule'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => ['required', 'exists:consultant_schedules,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $schedule = ConsultantSchedule::findOrFail($validated['schedule_id']);

        if (!$schedule->is_available || $schedule->calendar_blocked || $schedule->isBooked()) {
            return back()->with('error', 'この時間枠は既に予約済みまたはブロックされています。');
        }

        // コンサルタントの予約受付チェック
        $consultantProfile = $schedule->consultant->consultantProfile;
        if ($consultantProfile && !$consultantProfile->booking_acceptance_enabled) {
            return back()->with('error', 'このコンサルタントは現在予約を受け付けておりません。');
        }

        $maxPerDay = (int) SystemSetting::get('max_bookings_per_day', 8);
        $dailyCount = Booking::where('consultant_id', $schedule->user_id)
            ->where('booking_date', $schedule->date)
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        if ($dailyCount >= $maxPerDay) {
            return back()->with('error', 'このコンサルタントの本日の予約枠は上限に達しています。');
        }

        // Real-time calendar conflict check
        $profile = $schedule->consultant->consultantProfile;
        if ($profile && $profile->isGoogleConnected()) {
            $conflictCalendarIds = $profile->getConflictCalendarIds();
            if (!empty($conflictCalendarIds)) {
                try {
                    $conflict = app(GoogleCalendarService::class)->checkSlotConflict(
                        $profile->google_refresh_token,
                        $conflictCalendarIds,
                        $schedule->date->format('Y-m-d'),
                        $schedule->start_time,
                        $schedule->end_time
                    );
                    if ($conflict) {
                        return back()->with('error', 'この時間枠はコンサルタントの予定と重複しています。');
                    }
                } catch (\Exception $e) {
                    // API failure: allow booking (batch will catch conflicts later)
                }
            }
        }

        // Delete available slot event before creating booking
        if ($schedule->google_event_id) {
            try {
                app(GoogleCalendarService::class)->deleteAvailableSlotEvent($schedule);
            } catch (\Exception $e) {
                // Non-critical: continue with booking
            }
        }

        $booking = Booking::create([
            'user_id' => auth()->id(),
            'consultant_id' => $schedule->user_id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->date,
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'status' => 'approved',
            'notes' => $validated['notes'] ?? null,
            'amount' => $profile ? $profile->hourly_rate : 0,
        ]);

        try {
            $googleService = app(GoogleCalendarService::class);
            [$adminEventId, $consultantEventId] = $googleService->syncCreateEvent($booking);
            $booking->update([
                'google_event_id' => $adminEventId,
                'consultant_google_event_id' => $consultantEventId,
            ]);
        } catch (\Exception $e) {
            // Google Calendar integration is optional
        }

        AuditLog::log('booking_created', $booking);

        $notificationService = app(NotificationService::class);
        $notificationService->sendBookingConfirmation($booking);

        return redirect()->route('user.bookings.index')->with('success', '予約が確定しました。');
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $booking->load(['consultant.consultantProfile', 'schedule', 'review']);
        return view('user.bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$booking->canCancel()) {
            return back()->with('error', 'この予約はキャンセルできません。');
        }

        $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $oldStatus = $booking->status;
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

        // Restore available slot event on cancel
        $schedule = $booking->schedule;
        if ($schedule) {
            $consultantProfile = $booking->consultant->consultantProfile;
            if ($consultantProfile && $consultantProfile->sync_available_slots && !$schedule->google_event_id) {
                try {
                    app(GoogleCalendarService::class)->createAvailableSlotEvent($schedule);
                } catch (\Exception $e) {
                    // Non-critical
                }
            }
        }

        AuditLog::log('booking_cancelled', $booking, ['status' => $oldStatus], ['status' => 'cancelled']);

        $notificationService = app(NotificationService::class);
        $notificationService->sendBookingCancelled($booking);

        return redirect()->route('user.bookings.index')->with('success', '予約をキャンセルしました。');
    }
}
