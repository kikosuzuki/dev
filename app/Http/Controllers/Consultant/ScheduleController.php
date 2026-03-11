<?php

namespace App\Http\Controllers\Consultant;

use App\Helpers\JapaneseHolidays;
use App\Http\Controllers\Controller;
use App\Models\ConsultantSchedule;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $consultant = auth()->user();
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $schedules = ConsultantSchedule::where('user_id', $consultant->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->orderBy('start_time')
            ->with('bookings.user')
            ->get();

        $scheduleDates = $schedules->groupBy(function ($schedule) {
            return $schedule->date->format('Y-m-d');
        });

        return view('consultant.schedules.index', compact('schedules', 'scheduleDates', 'month', 'year'));
    }

    public function create()
    {
        $profile = auth()->user()->consultantProfile;
        return view('consultant.schedules.create', compact('profile'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'slots' => ['required', 'array', 'min:1'],
            'slots.*.start_time' => ['required', 'date_format:H:i'],
            'slots.*.end_time' => ['required', 'date_format:H:i', 'after:slots.*.start_time'],
        ]);

        $consultant = auth()->user();
        $profile = $consultant->consultantProfile;

        // Calendar conflict check
        $calendarEvents = [];
        $conflictCheckActive = false;
        if ($profile && $profile->isGoogleConnected()) {
            $conflictCalendarIds = $profile->getConflictCalendarIds();
            if (!empty($conflictCalendarIds)) {
                try {
                    $googleService = app(GoogleCalendarService::class);
                    $date = $validated['date'];
                    $timeMin = $date . 'T00:00:00+09:00';
                    $timeMax = $date . 'T23:59:59+09:00';
                    $calendarEvents = $googleService->listEventsFromMultipleCalendars(
                        $profile->google_refresh_token,
                        $conflictCalendarIds,
                        $timeMin,
                        $timeMax
                    );
                    $conflictCheckActive = true;
                } catch (\Exception $e) {
                    Log::warning('Calendar conflict check failed during individual creation', [
                        'error' => $e->getMessage(),
                    ]);
                    session()->flash('warning', 'カレンダーの重複チェックに失敗しました: ' . $e->getMessage());
                }
            }
        }

        $created = 0;
        $skipped = 0;
        $skippedDetails = [];

        foreach ($validated['slots'] as $slot) {
            $startTime = $slot['start_time'] . ':00';
            $endTime = $slot['end_time'] . ':00';

            if ($conflictCheckActive) {
                $slots = [['date' => $validated['date'], 'start_time' => $startTime, 'end_time' => $endTime]];
                $checked = app(GoogleCalendarService::class)->findConflicts($calendarEvents, $slots);
                if (!empty($checked[0]['has_conflict'])) {
                    $skipped++;
                    $skippedDetails[] = [
                        'date' => $validated['date'],
                        'time' => $slot['start_time'] . '〜' . $slot['end_time'],
                        'reason' => $checked[0]['conflicting_events'][0]['summary'] ?? '(予定)',
                    ];
                    continue;
                }
            }

            $schedule = ConsultantSchedule::create([
                'user_id' => $consultant->id,
                'date' => $validated['date'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_available' => true,
            ]);

            // Sync available slot to Google Calendar
            if ($profile && $profile->sync_available_slots) {
                try {
                    app(GoogleCalendarService::class)->createAvailableSlotEvent($schedule);
                } catch (\Exception $e) {
                    Log::warning('Failed to sync available slot event', ['schedule_id' => $schedule->id, 'error' => $e->getMessage()]);
                }
            }

            $created++;
        }

        if ($created > 0 && $skipped > 0) {
            $message = "{$created}件のスケジュールを登録しました。{$skipped}件はカレンダーの予定と重複するためスキップしました。";
        } elseif ($created > 0) {
            $message = "スケジュールを登録しました。（{$created}件）";
        } else {
            $message = "すべての枠がカレンダーの予定と重複するため、登録されませんでした。";
            return redirect()->route('consultant.schedules.index')
                ->with('error', $message)
                ->with('skipped_slots', $skippedDetails);
        }

        return redirect()->route('consultant.schedules.index')
            ->with('success', $message)
            ->with('skipped_slots', $skippedDetails);
    }

    public function destroy(ConsultantSchedule $schedule)
    {
        if ($schedule->user_id !== auth()->id()) {
            abort(403);
        }

        if ($schedule->isBooked()) {
            return back()->with('error', '予約が入っているスケジュールは削除できません。');
        }

        // Delete available slot event from Google Calendar
        if ($schedule->google_event_id) {
            try {
                app(GoogleCalendarService::class)->deleteAvailableSlotEvent($schedule);
            } catch (\Exception $e) {
                Log::warning('Failed to delete available slot event', ['schedule_id' => $schedule->id, 'error' => $e->getMessage()]);
            }
        }

        $schedule->delete();
        return back()->with('success', 'スケジュールを削除しました。');
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'days_of_week' => ['required', 'array', 'min:1'],
            'days_of_week.*' => ['string', 'in:sun,mon,tue,wed,thu,fri,sat'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_duration' => ['required', 'integer', 'min:15', 'max:240'],
            'skip_holidays' => ['boolean'],
            'enable_conflict_check' => ['boolean'],
        ]);

        $dayMap = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];
        $selectedDays = array_map(fn ($d) => $dayMap[$d], $validated['days_of_week']);
        $skipHolidays = !empty($validated['skip_holidays']);

        $consultant = auth()->user();
        $profile = $consultant->consultantProfile;
        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end = \Carbon\Carbon::parse($validated['end_date']);

        // Conflict checking setup (always use profile's conflict calendar settings)
        $enableConflictCheck = $validated['enable_conflict_check'] ?? true;
        $conflictCalendarIds = $profile ? $profile->getConflictCalendarIds() : [];
        $calendarEvents = [];
        $conflictCheckActive = false;

        if ($enableConflictCheck && $profile && $profile->isGoogleConnected() && !empty($conflictCalendarIds)) {
            try {
                $googleService = app(GoogleCalendarService::class);
                $timeMin = $start->format('Y-m-d') . 'T00:00:00+09:00';
                $timeMax = $end->format('Y-m-d') . 'T23:59:59+09:00';
                $calendarEvents = $googleService->listEventsFromMultipleCalendars(
                    $profile->google_refresh_token,
                    $conflictCalendarIds,
                    $timeMin,
                    $timeMax
                );
                $conflictCheckActive = true;
            } catch (\Exception $e) {
                Log::warning('Calendar conflict check failed during bulk creation', [
                    'error' => $e->getMessage(),
                ]);
                session()->flash('warning', 'カレンダーの重複チェックに失敗しました: ' . $e->getMessage());
            }
        }

        $created = 0;
        $skipped = 0;
        $skippedDetails = [];

        while ($start->lte($end)) {
            if (in_array($start->dayOfWeek, $selectedDays) && (!$skipHolidays || !JapaneseHolidays::isHoliday($start))) {
                $slotStart = \Carbon\Carbon::parse($validated['start_time']);
                $slotEnd = \Carbon\Carbon::parse($validated['end_time']);
                $duration = (int)$validated['slot_duration'];

                while ($slotStart->copy()->addMinutes($duration)->lte($slotEnd)) {
                    $slotStartTime = $slotStart->format('H:i:s');
                    $slotEndTime = $slotStart->copy()->addMinutes($duration)->format('H:i:s');
                    $dateStr = $start->toDateString();

                    // Check for calendar conflicts
                    $conflictEvent = null;
                    if ($conflictCheckActive) {
                        $slots = [['date' => $dateStr, 'start_time' => $slotStartTime, 'end_time' => $slotEndTime]];
                        $checked = app(GoogleCalendarService::class)->findConflicts($calendarEvents, $slots);
                        if (!empty($checked[0]['has_conflict'])) {
                            $conflictEvent = $checked[0]['conflicting_events'][0]['summary'] ?? '(予定)';
                        }
                    }

                    if ($conflictEvent) {
                        $skipped++;
                        $skippedDetails[] = [
                            'date' => $dateStr,
                            'time' => substr($slotStartTime, 0, 5) . '〜' . substr($slotEndTime, 0, 5),
                            'reason' => $conflictEvent,
                        ];
                    } else {
                        $schedule = ConsultantSchedule::firstOrCreate([
                            'user_id' => $consultant->id,
                            'date' => $dateStr,
                            'start_time' => $slotStartTime,
                            'end_time' => $slotEndTime,
                        ], [
                            'is_available' => true,
                        ]);

                        // Sync available slot to Google Calendar (only for newly created)
                        if ($schedule->wasRecentlyCreated && $profile && $profile->sync_available_slots) {
                            try {
                                app(GoogleCalendarService::class)->createAvailableSlotEvent($schedule);
                            } catch (\Exception $e) {
                                Log::warning('Failed to sync available slot event', ['schedule_id' => $schedule->id, 'error' => $e->getMessage()]);
                            }
                        }

                        $created++;
                    }

                    $slotStart->addMinutes($duration);
                }
            }
            $start->addDay();
        }

        $message = "{$created}件のスケジュールを一括登録しました。";
        if ($skipped > 0) {
            $message .= " {$skipped}件は既存の予定と重複するためスキップしました。";
        }

        return redirect()->route('consultant.schedules.index')
            ->with('success', $message)
            ->with('skipped_slots', $skippedDetails);
    }
}
