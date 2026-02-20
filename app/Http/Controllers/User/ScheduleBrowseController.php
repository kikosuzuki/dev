<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ConsultantSchedule;
use App\Models\User;
use Illuminate\Http\Request;

class ScheduleBrowseController extends Controller
{
    public function index(Request $request)
    {
        $query = ConsultantSchedule::with(['consultant.consultantProfile'])
            ->where('is_available', true)
            ->where('date', '>=', now()->toDateString())
            ->whereDoesntHave('bookings', function ($q) {
                $q->whereIn('status', ['pending', 'approved']);
            });

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
