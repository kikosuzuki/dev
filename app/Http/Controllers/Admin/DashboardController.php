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
            'consultation_success' => Booking::where('consultation_result', 'success')->count(),
            'consultation_failure' => Booking::where('consultation_result', 'failure')->count(),
            'consultation_pending' => Booking::where('consultation_result', 'pending')->count(),
        ];

        // Filter parameters
        $booking_type = $request->get('booking_type');
        $consultant_id = $request->get('consultant_id');
        $status = $request->get('status');
        $consultation_result = $request->get('consultation_result');
        $search = $request->get('search');

        $query = Booking::with(['user', 'consultant']);

        // Filter by booking type
        if ($booking_type === 'member') {
            $query->where('is_guest', false);
        } elseif ($booking_type === 'guest') {
            $query->where('is_guest', true);
        }

        // Filter by consultant
        if ($consultant_id) {
            $query->where('consultant_id', $consultant_id);
        }

        // Filter by status
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by consultation result
        if ($consultation_result && $consultation_result !== 'all') {
            if ($consultation_result === 'unrecorded') {
                $query->whereNull('consultation_result');
            } else {
                $query->where('consultation_result', $consultation_result);
            }
        }

        // Search by name or email (partial match)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                  ->orWhere('guest_email', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $recentBookings = $query->orderByDesc('created_at')
            ->paginate(10)
            ->appends($request->query());

        $consultants = User::where('role', 'consultant')->orderBy('name')->get();

        $monthlyBookings = Booking::selectRaw('DATE_FORMAT(booking_date, "%Y-%m") as month, COUNT(*) as count, SUM(amount) as revenue')
            ->whereIn('status', ['approved', 'completed'])
            ->groupBy('month')
            ->orderByDesc('month')
            ->limit(12)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'recentBookings', 'monthlyBookings', 'consultants',
            'booking_type', 'consultant_id', 'status', 'consultation_result',
            'search'
        ));
    }
}
