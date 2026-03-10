<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $consultant = auth()->user();

        // 月フィルター（YYYY-MM形式、デフォルトは今月）
        $selectedMonth = $request->get('month', now()->format('Y-m'));
        try {
            $targetDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        } catch (\Exception $e) {
            $targetDate = now()->startOfMonth();
            $selectedMonth = now()->format('Y-m');
        }

        $targetYear = $targetDate->year;
        $targetMonth = $targetDate->month;
        $isCurrentMonth = $targetDate->isSameMonth(now());

        // 本日の予約（月フィルターに関係なく常に表示）
        $todayBookings = Booking::where('consultant_id', $consultant->id)
            ->where('booking_date', now()->toDateString())
            ->whereIn('status', ['approved'])
            ->orderBy('start_time')
            ->with('user')
            ->get();

        // 選択月の予約一覧
        $monthlyBookings = Booking::where('consultant_id', $consultant->id)
            ->whereMonth('booking_date', $targetMonth)
            ->whereYear('booking_date', $targetYear)
            ->whereIn('status', ['approved', 'completed'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->with('user')
            ->get();

        // 統計情報（選択月ベース）
        $stats = [
            'total_bookings' => Booking::where('consultant_id', $consultant->id)->count(),
            'completed_bookings' => Booking::where('consultant_id', $consultant->id)
                ->whereMonth('booking_date', $targetMonth)
                ->whereYear('booking_date', $targetYear)
                ->where('status', 'completed')
                ->count(),
            'month_bookings' => Booking::where('consultant_id', $consultant->id)
                ->whereMonth('booking_date', $targetMonth)
                ->whereYear('booking_date', $targetYear)
                ->whereIn('status', ['approved', 'completed'])
                ->count(),
        ];

        // 前月・翌月のリンク用
        $prevMonth = $targetDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $targetDate->copy()->addMonth()->format('Y-m');
        $monthLabel = $targetDate->format('Y年n月');

        return view('consultant.dashboard', compact(
            'todayBookings',
            'monthlyBookings',
            'stats',
            'selectedMonth',
            'prevMonth',
            'nextMonth',
            'monthLabel',
            'isCurrentMonth'
        ));
    }
}
