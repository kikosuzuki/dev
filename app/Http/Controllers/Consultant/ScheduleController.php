<?php

namespace App\Http\Controllers\Consultant;

use App\Helpers\JapaneseHolidays;
use App\Http\Controllers\Controller;
use App\Models\ConsultantSchedule;
use Illuminate\Http\Request;

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
        return view('consultant.schedules.create');
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

        foreach ($validated['slots'] as $slot) {
            ConsultantSchedule::create([
                'user_id' => $consultant->id,
                'date' => $validated['date'],
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'is_available' => true,
            ]);
        }

        return redirect()->route('consultant.schedules.index')->with('success', 'スケジュールを登録しました。');
    }

    public function destroy(ConsultantSchedule $schedule)
    {
        if ($schedule->user_id !== auth()->id()) {
            abort(403);
        }

        if ($schedule->isBooked()) {
            return back()->with('error', '予約が入っているスケジュールは削除できません。');
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
        ]);

        $dayMap = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];
        $selectedDays = array_map(fn ($d) => $dayMap[$d], $validated['days_of_week']);
        $skipHolidays = !empty($validated['skip_holidays']);

        $consultant = auth()->user();
        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end = \Carbon\Carbon::parse($validated['end_date']);
        $count = 0;

        while ($start->lte($end)) {
            if (in_array($start->dayOfWeek, $selectedDays) && (!$skipHolidays || !JapaneseHolidays::isHoliday($start))) {
                $slotStart = \Carbon\Carbon::parse($validated['start_time']);
                $slotEnd = \Carbon\Carbon::parse($validated['end_time']);
                $duration = (int)$validated['slot_duration'];

                while ($slotStart->copy()->addMinutes($duration)->lte($slotEnd)) {
                    ConsultantSchedule::firstOrCreate([
                        'user_id' => $consultant->id,
                        'date' => $start->toDateString(),
                        'start_time' => $slotStart->format('H:i:s'),
                        'end_time' => $slotStart->copy()->addMinutes($duration)->format('H:i:s'),
                    ], [
                        'is_available' => true,
                    ]);
                    $slotStart->addMinutes($duration);
                    $count++;
                }
            }
            $start->addDay();
        }

        return redirect()->route('consultant.schedules.index')
            ->with('success', "{$count}件のスケジュールを一括登録しました。");
    }
}
