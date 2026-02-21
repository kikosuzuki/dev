<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultantSchedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = ConsultantSchedule::with(['consultant.consultantProfile'])
            ->where('is_available', true)
            ->where('date', '>=', now()->toDateString())
            ->whereDoesntHave('bookings', function ($q) {
                $q->whereIn('status', ['pending', 'approved']);
            })
            ->withinDailyLimit();

        if ($request->filled('consultant')) {
            $query->where('user_id', $request->consultant);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Cap total displayed schedules at 50
        $maxDisplay = 50;
        $limitedIds = (clone $query)->orderBy('date')
            ->orderBy('start_time')
            ->limit($maxDisplay)
            ->pluck('id');
        $schedules = ConsultantSchedule::with(['consultant.consultantProfile'])
            ->whereIn('id', $limitedIds)
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(30);

        $consultants = \App\Models\User::where('role', 'consultant')
            ->orderBy('name')
            ->get();

        return view('admin.schedules.index', compact('schedules', 'consultants'));
    }
}
