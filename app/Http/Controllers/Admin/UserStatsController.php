<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserStatsController extends Controller
{
    public function index()
    {
        $now = now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        $users = User::where('role', 'user')
            ->withCount([
                'bookingsAsUser as this_month_bookings' => function ($q) use ($currentMonth, $currentYear) {
                    $q->whereMonth('booking_date', $currentMonth)
                      ->whereYear('booking_date', $currentYear)
                      ->whereIn('status', ['pending', 'approved', 'completed']);
                },
                'bookingsAsUser as this_year_bookings' => function ($q) use ($currentYear) {
                    $q->whereYear('booking_date', $currentYear)
                      ->whereIn('status', ['pending', 'approved', 'completed']);
                },
                'bookingsAsUser as completed_this_month' => function ($q) use ($currentMonth, $currentYear) {
                    $q->whereMonth('booking_date', $currentMonth)
                      ->whereYear('booking_date', $currentYear)
                      ->where('status', 'completed');
                },
                'bookingsAsUser as completed_this_year' => function ($q) use ($currentYear) {
                    $q->whereYear('booking_date', $currentYear)
                      ->where('status', 'completed');
                },
                'bookingsAsUser as cancelled_this_year' => function ($q) use ($currentYear) {
                    $q->whereYear('booking_date', $currentYear)
                      ->where('status', 'cancelled');
                },
            ])
            ->orderByDesc('this_month_bookings')
            ->paginate(20);

        // Summary stats
        $summary = [
            'total_users' => User::where('role', 'user')->count(),
            'active_this_month' => Booking::whereMonth('booking_date', $currentMonth)
                ->whereYear('booking_date', $currentYear)
                ->whereIn('status', ['pending', 'approved', 'completed'])
                ->distinct('user_id')
                ->count('user_id'),
            'total_bookings_this_month' => Booking::whereMonth('booking_date', $currentMonth)
                ->whereYear('booking_date', $currentYear)
                ->whereIn('status', ['pending', 'approved', 'completed'])
                ->count(),
            'total_bookings_this_year' => Booking::whereYear('booking_date', $currentYear)
                ->whereIn('status', ['pending', 'approved', 'completed'])
                ->count(),
        ];

        return view('admin.user-stats.index', compact('users', 'summary', 'currentMonth', 'currentYear'));
    }
}
