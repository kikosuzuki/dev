<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ConsultantSchedule;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = ConsultantSchedule::with(['consultant.consultantProfile'])
            ->where('is_available', true)
            ->upcoming()
            ->whereDoesntHave('bookings', function ($q) {
                $q->whereIn('status', ['pending', 'approved']);
            })
            ->withinDailyLimit();

        if ($request->filled('consultant')) {
            $query->where('user_id', $request->consultant);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $perPage = (int) SystemSetting::get('schedule_per_page', 30);
        $schedules = $query->orderBy('date')
            ->orderBy('start_time')
            ->paginate($perPage);

        $consultants = User::where('role', 'consultant')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.schedules.index', compact('schedules', 'consultants'));
    }

    public function create()
    {
        $consultants = User::where('role', 'consultant')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.schedules.create', compact('consultants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'consultant_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        // コンサルタントであることを確認
        $consultant = User::where('id', $validated['consultant_id'])
            ->where('role', 'consultant')
            ->firstOrFail();

        // 重複チェック
        $exists = ConsultantSchedule::where('user_id', $consultant->id)
            ->where('date', $validated['date'])
            ->where('start_time', $validated['start_time'])
            ->where('end_time', $validated['end_time'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'この時間枠は既に登録されています。');
        }

        $schedule = ConsultantSchedule::create([
            'user_id' => $consultant->id,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_available' => true,
        ]);

        AuditLog::log('admin_schedule_created', $schedule);

        return redirect()->route('admin.schedules.index')
            ->with('success', "{$consultant->name}さんの予約枠を追加しました。");
    }
}
