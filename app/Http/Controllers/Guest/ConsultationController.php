<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\ConsultantSchedule;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\GoogleCalendarService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        // Check if booking acceptance is enabled
        if (SystemSetting::get('booking_acceptance_enabled', '1') !== '1') {
            return view('guest.consultation.closed');
        }

        $disclosureDays = (int) SystemSetting::get('schedule_disclosure_days', 30);
        $maxDate = now()->addDays($disclosureDays)->toDateString();
        $hoursFromNow = (int) SystemSetting::get('hours_from_now', 2);
        $minDateTime = now()->addHours($hoursFromNow);

        $query = ConsultantSchedule::where('is_available', true)
            ->notCalendarBlocked()
            ->whereHas('consultant', function ($q) {
                $q->where('is_active', true);
            })
            ->upcoming()
            ->where('date', '<=', $maxDate)
            ->where(function ($q) use ($minDateTime) {
                $q->where('date', '>', $minDateTime->toDateString())
                  ->orWhere(function ($q2) use ($minDateTime) {
                      $q2->where('date', $minDateTime->toDateString())
                         ->where('start_time', '>=', $minDateTime->format('H:i:s'));
                  });
            })
            ->whereDoesntHave('bookings', function ($q) {
                $q->whereIn('status', ['pending', 'approved']);
            })
            ->withinDailyLimit()
            ->acceptingBookings();

        if ($request->filled('consultant')) {
            $query->where('user_id', $request->consultant);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $view = $request->get('view', 'list');
        $intro = $request->get('intro');

        $consultants = User::where('role', 'consultant')
            ->where('is_active', true)
            ->whereHas('consultantProfile', fn ($q) => $q->where('booking_acceptance_enabled', true))
            ->orderBy('name')
            ->get();

        // List view: paginated
        $schedules = (clone $query)->orderBy('date')
            ->orderBy('start_time')
            ->paginate(30);

        // Calendar view: grouped by date
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $calendarSchedules = (clone $query)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn ($s) => $s->date->format('Y-m-d'));

        return response()
            ->view('guest.consultation.index', compact('schedules', 'calendarSchedules', 'view', 'year', 'month', 'intro', 'consultants'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function create(Request $request, ConsultantSchedule $schedule)
    {
        if (!$schedule->is_available || $schedule->isBooked()) {
            return back()->with('error', 'この時間枠は既に予約済みです。');
        }

        $intro = $request->get('intro');
        $schedule->load('consultant.consultantProfile');

        return view('guest.consultation.create', compact('schedule', 'intro'));
    }

    public function store(Request $request)
    {
        if (SystemSetting::get('booking_acceptance_enabled', '1') !== '1') {
            return redirect()->route('consultation.index')->with('error', '現在予約の受付を停止しております。');
        }

        $validated = $request->validate([
            'schedule_id' => ['required', 'exists:consultant_schedules,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'guest_referrer' => ['nullable', 'string', 'max:255'],
        ]);

        $schedule = ConsultantSchedule::findOrFail($validated['schedule_id']);

        if (!$schedule->is_available || $schedule->isBooked()) {
            return back()->with('error', 'この時間枠は既に予約済みです。');
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
            return back()->with('error', 'このコンサルタントの予約枠は上限に達しています。別の日時をお選びください。');
        }

        // Real-time calendar conflict check
        if ($consultantProfile && $consultantProfile->isGoogleConnected()) {
            $conflictCalendarIds = $consultantProfile->getConflictCalendarIds();
            if (!empty($conflictCalendarIds)) {
                try {
                    $conflict = app(GoogleCalendarService::class)->checkSlotConflict(
                        $consultantProfile->google_refresh_token,
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
            'user_id' => null,
            'consultant_id' => $schedule->user_id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->date,
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'status' => 'approved',
            'notes' => $validated['notes'] ?? null,
            'amount' => 0,
            'is_guest' => true,
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'guest_phone' => $validated['guest_phone'],
            'guest_referrer' => $validated['guest_referrer'] ?? null,
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

        AuditLog::log('guest_booking_created', $booking);

        $notificationService = app(NotificationService::class);
        $notificationService->sendBookingConfirmation($booking);

        return redirect()->route('consultation.complete', $booking)->with('success', '個別相談の予約が確定しました。');
    }

    public function complete(Booking $booking)
    {
        if (!$booking->isGuest()) {
            abort(404);
        }

        return view('guest.consultation.complete', compact('booking'));
    }
}
