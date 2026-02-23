<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $consultationResult = $request->get('consultation_result');

        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'total_consultants' => User::where('role', 'consultant')->count(),
            'total_bookings' => Booking::count(),
            'active_bookings' => Booking::where('status', 'approved')->count(),
            'this_month_bookings' => Booking::whereMonth('booking_date', now()->month)
                ->whereYear('booking_date', now()->year)->count(),
            'this_month_revenue' => Booking::whereMonth('booking_date', now()->month)
                ->whereYear('booking_date', now()->year)
                ->whereIn('status', ['approved', 'completed'])
                ->sum('amount'),
        ];

        $query = Booking::with(['user', 'consultant'])
            ->orderByDesc('created_at');

        if ($consultationResult && $consultationResult !== 'all') {
            $query->where('is_guest', true);
            if ($consultationResult === 'unrecorded') {
                $query->whereNull('consultation_result');
            } else {
                $query->where('consultation_result', $consultationResult);
            }
        }

        $recentBookings = $query->paginate(10)->appends($request->query());

        $monthlyBookings = Booking::selectRaw('DATE_FORMAT(booking_date, "%Y-%m") as month, COUNT(*) as count, SUM(amount) as revenue')
            ->whereIn('status', ['approved', 'completed'])
            ->groupBy('month')
            ->orderByDesc('month')
            ->limit(12)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentBookings', 'monthlyBookings', 'consultationResult'));
    }
}
