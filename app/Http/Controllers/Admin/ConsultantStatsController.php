<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class ConsultantStatsController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $consultants = User::where('role', 'consultant')
            ->with('consultantProfile')
            ->get()
            ->map(function ($consultant) use ($month, $year) {
                $bookings = Booking::where('consultant_id', $consultant->id)
                    ->whereYear('booking_date', $year)
                    ->whereMonth('booking_date', $month);

                $consultant->stats = [
                    'total_bookings' => (clone $bookings)->count(),
                    'completed' => (clone $bookings)->where('status', 'completed')->count(),
                    'cancelled' => (clone $bookings)->where('status', 'cancelled')->count(),
                    'consultation_success' => (clone $bookings)->where('consultation_result', 'success')->count(),
                    'consultation_failure' => (clone $bookings)->where('consultation_result', 'failure')->count(),
                    'consultation_pending' => (clone $bookings)->where('consultation_result', 'pending')->count(),
                    'revenue' => (clone $bookings)->whereIn('status', ['approved', 'completed'])->sum('amount'),
                    'working_hours' => $this->calculateWorkingHours($consultant->id, $month, $year),
                ];

                return $consultant;
            });

        return view('admin.stats.index', compact('consultants', 'month', 'year'));
    }

    public function show(User $consultant, Request $request)
    {
        if (!$consultant->isConsultant()) {
            abort(404);
        }

        $year = $request->get('year', now()->year);

        $monthlyStats = collect(range(1, 12))->map(function ($month) use ($consultant, $year) {
            $bookings = Booking::where('consultant_id', $consultant->id)
                ->whereYear('booking_date', $year)
                ->whereMonth('booking_date', $month);

            return [
                'month' => $month,
                'total' => (clone $bookings)->count(),
                'completed' => (clone $bookings)->where('status', 'completed')->count(),
                'cancelled' => (clone $bookings)->where('status', 'cancelled')->count(),
                'consultation_success' => (clone $bookings)->where('consultation_result', 'success')->count(),
                'consultation_failure' => (clone $bookings)->where('consultation_result', 'failure')->count(),
                'consultation_pending' => (clone $bookings)->where('consultation_result', 'pending')->count(),
                'revenue' => (clone $bookings)->whereIn('status', ['approved', 'completed'])->sum('amount'),
                'hours' => $this->calculateWorkingHours($consultant->id, $month, $year),
            ];
        });

        $consultant->load('consultantProfile');

        return view('admin.stats.show', compact('consultant', 'monthlyStats', 'year'));
    }

    private function calculateWorkingHours(int $consultantId, int $month, int $year): float
    {
        $bookings = Booking::where('consultant_id', $consultantId)
            ->whereYear('booking_date', $year)
            ->whereMonth('booking_date', $month)
            ->whereIn('status', ['approved', 'completed'])
            ->get();

        $totalMinutes = 0;
        foreach ($bookings as $booking) {
            $start = \Carbon\Carbon::parse($booking->start_time);
            $end = \Carbon\Carbon::parse($booking->end_time);
            $totalMinutes += $start->diffInMinutes($end);
        }

        return round($totalMinutes / 60, 1);
    }
}
