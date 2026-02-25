<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ConsultantSchedule;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $upcomingBookings = Booking::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('booking_date', '>=', now()->toDateString())
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->with(['consultant.consultantProfile'])
            ->limit(5)
            ->get();

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $availableSchedules = ConsultantSchedule::where('is_available', true)
            ->upcoming()
            ->acceptingBookings()
            ->whereDoesntHave('bookings', function ($q) {
                $q->whereIn('status', ['pending', 'approved']);
            })
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with(['consultant.consultantProfile'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $scheduleDates = $availableSchedules->groupBy(function ($schedule) {
            return $schedule->date->format('Y-m-d');
        });

        $featuredConsultants = User::where('role', 'consultant')
            ->where('is_active', true)
            ->whereHas('consultantProfile', fn($q) => $q->where('is_featured', true))
            ->with('consultantProfile')
            ->limit(6)
            ->get();

        return view('user.dashboard', compact(
            'upcomingBookings',
            'availableSchedules',
            'scheduleDates',
            'featuredConsultants',
            'month',
            'year'
        ));
    }
}
