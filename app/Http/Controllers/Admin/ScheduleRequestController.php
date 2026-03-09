<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\ConsultantSchedule;
use App\Models\ScheduleRequest;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\GoogleCalendarService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ScheduleRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $query = ScheduleRequest::query();

        if ($status === 'pending') {
            $query->pending();
        } elseif ($status === 'processed') {
            $query->processed();
        }

        $scheduleRequests = $query->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->query());

        $pendingCount = ScheduleRequest::pending()->count();

        $consultants = User::where('role', 'consultant')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.schedule-requests.index', compact('scheduleRequests', 'status', 'pendingCount', 'consultants'));
    }

    public function updateStatus(Request $request, ScheduleRequest $scheduleRequest)
    {
        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $scheduleRequest->update([
            'status' => 'processed',
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'リクエストを対応済みにしました。');
    }

    public function sendReply(Request $request, ScheduleRequest $scheduleRequest)
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        try {
            Mail::raw($request->body, function ($message) use ($scheduleRequest, $request) {
                $message->to($scheduleRequest->guest_email)
                    ->subject($request->subject);
            });

            $scheduleRequest->update([
                'status' => 'processed',
                'admin_notes' => ($scheduleRequest->admin_notes ? $scheduleRequest->admin_notes . "\n" : '')
                    . '【' . now()->format('m/d H:i') . ' メール返信済み】' . $request->subject,
            ]);

            return back()->with('success', "{$scheduleRequest->guest_name}様にメールを送信しました。");
        } catch (\Exception $e) {
            Log::error('Schedule request reply email failed', [
                'schedule_request_id' => $scheduleRequest->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'メール送信に失敗しました。');
        }
    }

    public function createBooking(Request $request, ScheduleRequest $scheduleRequest)
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

        // 既存枠チェック
        $existingSchedule = ConsultantSchedule::where('user_id', $consultant->id)
            ->where('date', $validated['date'])
            ->where('start_time', $validated['start_time'])
            ->where('end_time', $validated['end_time'])
            ->first();

        if ($existingSchedule) {
            if ($existingSchedule->isBooked()) {
                return back()->with('error', 'この時間枠は既に予約済みです。別の日時を選択してください。');
            }
            $schedule = $existingSchedule;
        } else {
            // 新規枠作成
            $schedule = ConsultantSchedule::create([
                'user_id' => $consultant->id,
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'is_available' => true,
            ]);
        }

        // 1日の予約上限チェック
        $maxPerDay = (int) SystemSetting::get('max_bookings_per_day', 8);
        $dailyCount = Booking::where('consultant_id', $consultant->id)
            ->where('booking_date', $validated['date'])
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        if ($dailyCount >= $maxPerDay) {
            return back()->with('error', 'このコンサルタントの1日の予約上限に達しています。');
        }

        // ゲスト予約作成
        $booking = Booking::create([
            'user_id' => null,
            'consultant_id' => $consultant->id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->date,
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'status' => 'approved',
            'is_guest' => true,
            'guest_name' => $scheduleRequest->guest_name,
            'guest_email' => $scheduleRequest->guest_email,
            'guest_phone' => $scheduleRequest->guest_phone,
            'amount' => 0,
            'admin_notes' => '日程リクエスト #' . $scheduleRequest->id . ' から作成',
        ]);

        // Google Calendar同期
        try {
            $googleService = app(GoogleCalendarService::class);
            [$adminEventId, $consultantEventId] = $googleService->syncCreateEvent($booking);
            $booking->update([
                'google_event_id' => $adminEventId,
                'consultant_google_event_id' => $consultantEventId,
            ]);
        } catch (\Exception $e) {
            // Google Calendar連携はオプション
        }

        // 監査ログ
        AuditLog::log('booking_created_from_schedule_request', $booking);

        // 通知送信（メール・Chatwork等）
        try {
            $notificationService = app(NotificationService::class);
            $notificationService->sendBookingConfirmation($booking);
        } catch (\Exception $e) {
            Log::error('Booking notification failed for schedule request', [
                'schedule_request_id' => $scheduleRequest->id,
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        // リクエストを対応済みに更新
        $scheduleRequest->update([
            'status' => 'processed',
            'admin_notes' => ($scheduleRequest->admin_notes ? $scheduleRequest->admin_notes . "\n" : '')
                . '【' . now()->format('m/d H:i') . ' 予約作成済み】'
                . $consultant->name . ' / ' . $validated['date'] . ' ' . $validated['start_time'] . '〜' . $validated['end_time'],
        ]);

        return back()->with('success', "{$scheduleRequest->guest_name}様の予約を作成しました（{$consultant->name} / {$validated['date']} {$validated['start_time']}〜）。");
    }
}
