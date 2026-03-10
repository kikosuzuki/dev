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
        ];

        // Available years for the selector (from earliest booking year to current year)
        $driver = Booking::getConnectionResolver()->connection()->getDriverName();
        $earliestYear = $driver === 'sqlite'
            ? Booking::min(DB::raw('strftime("%Y", booking_date)'))
            : Booking::selectRaw('MIN(YEAR(booking_date)) as min_year')->value('min_year');
        $earliestYear = $earliestYear ? (int) $earliestYear : $now->year;
        $availableYears = range($now->year, $earliestYear);

        return view('admin.user-stats.index', compact(
            'users', 'summary', 'selectedMonth', 'selectedYear', 'availableYears'
        ));
    }
}
