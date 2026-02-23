<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserStatsController extends Controller
{
    public function index(Request $request)
    {
        $now = now();
        $selectedMonth = (int) $request->get('month', $now->month);
        $selectedYear = (int) $request->get('year', $now->year);

        $users = User::where('role', 'user')
            ->withCount([
                'bookingsAsUser as this_month_bookings' => function ($q) use ($selectedMonth, $selectedYear) {
                    $q->whereMonth('booking_date', $selectedMonth)
                      ->whereYear('booking_date', $selectedYear)
                      ->whereIn('status', ['pending', 'approved', 'completed']);
                },
                'bookingsAsUser as this_year_bookings' => function ($q) use ($selectedYear) {
                    $q->whereYear('booking_date', $selectedYear)
                      ->whereIn('status', ['pending', 'approved', 'completed']);
                },
                'bookingsAsUser as completed_this_month' => function ($q) use ($selectedMonth, $selectedYear) {
                    $q->whereMonth('booking_date', $selectedMonth)
                      ->whereYear('booking_date', $selectedYear)
                      ->where('status', 'completed');
                },
                'bookingsAsUser as completed_this_year' => function ($q) use ($selectedYear) {
                    $q->whereYear('booking_date', $selectedYear)
                      ->where('status', 'completed');
                },
                'bookingsAsUser as cancelled_this_year' => function ($q) use ($selectedYear) {
                    $q->whereYear('booking_date', $selectedYear)
                      ->where('status', 'cancelled');
                },
                'bookingsAsUser as consultation_success' => function ($q) use ($selectedYear) {
                    $q->whereYear('booking_date', $selectedYear)
                      ->where('consultation_result', 'success');
                },
                'bookingsAsUser as consultation_failure' => function ($q) use ($selectedYear) {
                    $q->whereYear('booking_date', $selectedYear)
                      ->where('consultation_result', 'failure');
                },
                'bookingsAsUser as consultation_pending' => function ($q) use ($selectedYear) {
                    $q->whereYear('booking_date', $selectedYear)
                      ->where('consultation_result', 'pending');
                },
            ])
            ->orderByDesc('this_month_bookings')
            ->paginate(20)
            ->appends($request->query());

        // Summary stats
        $summary = [
            'total_users' => User::where('role', 'user')->count(),
            'active_this_month' => Booking::whereMonth('booking_date', $selectedMonth)
                ->whereYear('booking_date', $selectedYear)
                ->whereIn('status', ['pending', 'approved', 'completed'])
                ->distinct('user_id')
                ->count('user_id'),
            'total_bookings_this_month' => Booking::whereMonth('booking_date', $selectedMonth)
                ->whereYear('booking_date', $selectedYear)
                ->whereIn('status', ['pending', 'approved', 'completed'])
                ->count(),
            'total_bookings_this_year' => Booking::whereYear('booking_date', $selectedYear)
                ->whereIn('status', ['pending', 'approved', 'completed'])
                ->count(),
            'consultation_success' => Booking::whereYear('booking_date', $selectedYear)
                ->where('consultation_result', 'success')->count(),
            'consultation_failure' => Booking::whereYear('booking_date', $selectedYear)
                ->where('consultation_result', 'failure')->count(),
            'consultation_pending' => Booking::whereYear('booking_date', $selectedYear)
                ->where('consultation_result', 'pending')->count(),
        ];

        // Available years for the selector (from earliest booking year to current year)
        $earliestYear = Booking::min(DB::raw('strftime("%Y", booking_date)'));
        $earliestYear = $earliestYear ? (int) $earliestYear : $now->year;
        $availableYears = range($now->year, $earliestYear);

        return view('admin.user-stats.index', compact(
            'users', 'summary', 'selectedMonth', 'selectedYear', 'availableYears'
        ));
    }
}
