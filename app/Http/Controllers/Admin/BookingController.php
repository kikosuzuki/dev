<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '1month');
        $consultant_id = $request->get('consultant_id');
        $status = $request->get('status');

        $now = now();
        $endDate = match ($period) {
            '1week' => $now->copy()->addWeek(),
            '2weeks' => $now->copy()->addWeeks(2),
            '1month' => $now->copy()->addMonth(),
            '2months' => $now->copy()->addMonths(2),
            '3months' => $now->copy()->addMonths(3),
            '6months' => $now->copy()->addMonths(6),
            'all' => null,
            default => $now->copy()->addMonth(),
        };

        $query = Booking::with(['user', 'consultant', 'schedule'])
            ->whereIn('status', ['pending', 'approved']);

        if ($endDate) {
            $query->where('booking_date', '>=', $now->toDateString())
                  ->where('booking_date', '<=', $endDate->toDateString());
        } else {
            $query->where('booking_date', '>=', $now->toDateString());
        }

        if ($consultant_id) {
            $query->where('consultant_id', $consultant_id);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $bookings = $query->orderBy('booking_date')
            ->orderBy('start_time')
            ->paginate(20)
            ->appends($request->query());

        $totalCount = $query->getQuery()->getCountForPagination();

        $consultants = User::where('role', 'consultant')
            ->orderBy('name')
            ->get();

        $periods = [
            '1week' => '1週間',
            '2weeks' => '2週間',
            '1month' => '1ヶ月',
            '2months' => '2ヶ月',
            '3months' => '3ヶ月',
            '6months' => '6ヶ月',
            'all' => '全期間',
        ];

        return view('admin.bookings.index', compact('bookings', 'consultants', 'periods', 'period', 'consultant_id', 'status'));
    }
}
