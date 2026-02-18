<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $consultant = auth()->user();

        $todayBookings = Booking::where('consultant_id', $consultant->id)
            ->where('booking_date', now()->toDateString())
            ->whereIn('status', ['approved'])
            ->orderBy('start_time')
            ->with('user')
            ->get();

        $pendingBookings = Booking::where('consultant_id', $consultant->id)
            ->where('status', 'pending')
            ->orderBy('booking_date')
            ->with('user')
            ->get();

        $upcomingBookings = Booking::where('consultant_id', $consultant->id)
            ->whereIn('status', ['approved'])
            ->where('booking_date', '>=', now()->toDateString())
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->with('user')
            ->limit(10)
            ->get();

        $stats = [
            'total_bookings' => Booking::where('consultant_id', $consultant->id)->count(),
            'completed_bookings' => Booking::where('consultant_id', $consultant->id)->where('status', 'completed')->count(),
            'this_month_bookings' => Booking::where('consultant_id', $consultant->id)
                ->whereMonth('booking_date', now()->month)
                ->whereYear('booking_date', now()->year)
                ->whereIn('status', ['approved', 'completed'])
                ->count(),
            'this_month_revenue' => Booking::where('consultant_id', $consultant->id)
                ->whereMonth('booking_date', now()->month)
                ->whereYear('booking_date', now()->year)
                ->whereIn('status', ['approved', 'completed'])
                ->sum('amount'),
        ];

        return view('consultant.dashboard', compact('todayBookings', 'pendingBookings', 'upcomingBookings', 'stats'));
    }
}
