<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ConsultantSchedule;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;

class ScheduleBrowseController extends Controller
{
    public function index(Request $request)
    {
        // Check if booking acceptance is enabled
        if (SystemSetting::get('booking_acceptance_enabled', '1') !== '1') {
            return view('user.schedules.index', ['schedules' => collect(), 'consultants' => collect(), 'acceptanceClosed' => true]);
        }

        $disclosureDays = (int) SystemSetting::get('schedule_disclosure_days', 30);
        $maxDate = now()->addDays($disclosureDays)->toDateString();
        $hoursFromNow = (int) SystemSetting::get('hours_from_now', 2);
        $minDateTime = now()->addHours($hoursFromNow);

        $query = ConsultantSchedule::with(['consultant.consultantProfile'])
            ->where('is_available', true)
            ->notCalendarBlocked()
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

        $schedules = $query->orderBy('date')
            ->orderBy('start_time')
            ->paginate(30);

        $consultants = User::where('role', 'consultant')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('user.schedules.index', compact('schedules', 'consultants'));
    }
}
