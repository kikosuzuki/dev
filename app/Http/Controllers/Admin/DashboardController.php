<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'total_consultants' => User::where('role', 'consultant')->count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'this_month_bookings' => Booking::whereMonth('booking_date', now()->month)
                ->whereYear('booking_date', now()->year)->count(),
            'this_month_revenue' => Booking::whereMonth('booking_date', now()->month)
                ->whereYear('booking_date', now()->year)
                ->whereIn('status', ['approved', 'completed'])
                ->sum('amount'),
        ];

        $recentBookings = Booking::with(['user', 'consultant'])
            ->orderByDesc('created_at')
            ->paginate(10);

        $monthlyBookings = Booking::selectRaw('DATE_FORMAT(booking_date, "%Y-%m") as month, COUNT(*) as count, SUM(amount) as revenue')
            ->whereIn('status', ['approved', 'completed'])
            ->groupBy('month')
            ->orderByDesc('month')
            ->limit(12)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentBookings', 'monthlyBookings'));
    }
}
