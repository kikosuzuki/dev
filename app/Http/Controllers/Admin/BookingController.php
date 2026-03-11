<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\ConsultantSchedule;
use App\Models\GuestMessageTemplate;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\GoogleCalendarService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '1month');
        $consultant_id = $request->get('consultant_id');
        $status = $request->get('status');
        $booking_type = $request->get('booking_type', 'all');
        $consultation_result = $request->get('consultation_result');
        $search = $request->get('search');

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

        $query = Booking::with(['user', 'consultant', 'schedule']);

        // Status filter (default: approved)
        if (!$status || $status === 'approved') {
            $query->where('status', 'approved');
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        } elseif ($status === 'cancelled') {
            $query->where('status', 'cancelled');
        } else {
            // 'all' - no status filter
            $query->whereIn('status', ['approved', 'completed', 'cancelled']);
        }

        if ($endDate) {
            $query->where('booking_date', '>=', $now->toDateString())
                  ->where('booking_date', '<=', $endDate->toDateString());
        } else {
            $query->where('booking_date', '>=', $now->toDateString());
        }

        if ($consultant_id) {
            $query->where('consultant_id', $consultant_id);
        }

        // Filter by booking type (member / guest)
        if ($booking_type === 'member') {
            $query->where('is_guest', false);
        } elseif ($booking_type === 'guest') {
            $query->where('is_guest', true);
        }

        // Filter by consultation result
        if ($consultation_result && $consultation_result !== 'all') {
            if ($consultation_result === 'unrecorded') {
                $query->whereNull('consultation_result');
            } else {
                $query->where('consultation_result', $consultation_result);
            }
        }

        // Search by name or email
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

        $emailTemplates = GuestMessageTemplate::ordered()->get()
            ->map(fn ($t) => [
                'label' => $t->name,
                'subject' => $t->subject ?? '',
                'body' => $t->body ?? '',
            ])->toArray();

        return view('admin.bookings.index', compact('bookings', 'consultants', 'periods', 'period', 'consultant_id', 'status', 'booking_type', 'consultation_result', 'search', 'emailTemplates'));
    }

    public function exportCsv(Request $request)
    {
        $period = $request->get('period', '1month');
        $consultant_id = $request->get('consultant_id');
        $status = $request->get('status');
        $booking_type = $request->get('booking_type', 'all');
        $consultation_result = $request->get('consultation_result');
        $search = $request->get('search');

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

        $query = Booking::with(['user', 'consultant', 'schedule']);

        if (!$status || $status === 'approved') {
            $query->where('status', 'approved');
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        } elseif ($status === 'cancelled') {
            $query->where('status', 'cancelled');
        } else {
            $query->whereIn('status', ['approved', 'completed', 'cancelled']);
        }

        if ($endDate) {
            $query->where('booking_date', '>=', $now->toDateString())
                  ->where('booking_date', '<=', $endDate->toDateString());
        } else {
            $query->where('booking_date', '>=', $now->toDateString());
        }

        if ($consultant_id) {
            $query->where('consultant_id', $consultant_id);
        }

        if ($booking_type === 'member') {
            $query->where('is_guest', false);
        } elseif ($booking_type === 'guest') {
            $query->where('is_guest', true);
        }

        if ($consultation_result && $consultation_result !== 'all') {
            if ($consultation_result === 'unrecorded') {
                $query->whereNull('consultation_result');
            } else {
                $query->where('consultation_result', $consultation_result);
            }
        }

        // Search by name or email
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

        $bookings = $query->orderBy('booking_date')->orderBy('start_time')->get();

        $statusLabels = ['approved' => '確定', 'completed' => '完了', 'cancelled' => 'キャンセル'];
        $resultLabels = ['success' => '成約', 'failure' => '不成約', 'pending' => '検討中'];

        $callback = function () use ($bookings, $statusLabels, $resultLabels) {
            $file = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                '予約ID', '種別', '予約者名', 'メール', '電話番号', '紹介者',
                'コンサルタント', '予約日', '開始時間', '終了時間',
                'ステータス', '相談結果', '備考', '管理メモ',
            ]);

            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->id,
                    $booking->is_guest ? '個別相談' : '会員',
                    $booking->is_guest ? $booking->guest_name : ($booking->user->name ?? ''),
                    $booking->is_guest ? $booking->guest_email : ($booking->user->email ?? ''),
                    $booking->is_guest ? $booking->guest_phone : ($booking->user->phone ?? ''),
                    $booking->guest_referrer ?? '',
                    $booking->consultant->name ?? '',
                    $booking->booking_date->format('Y/m/d'),
                    \Carbon\Carbon::parse($booking->start_time)->format('H:i'),
                    \Carbon\Carbon::parse($booking->end_time)->format('H:i'),
                    $statusLabels[$booking->status] ?? $booking->status,
                    $resultLabels[$booking->consultation_result] ?? '',
                    $booking->notes ?? '',
                    $booking->admin_notes ?? '',
                ]);
            }

            fclose($file);
        };

        $filename = '予約一覧_' . now()->format('Ymd_His') . '.csv';

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function create()
    {
        $consultants = User::where('role', 'consultant')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $users = User::where('role', 'user')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.bookings.create', compact('consultants', 'users'));
    }

    public function getSchedules(Request $request)
    {
        $request->validate([
            'consultant_id' => ['required', 'exists:users,id'],
        ]);

        $schedules = ConsultantSchedule::where('user_id', $request->consultant_id)
            ->where('is_available', true)
            ->notCalendarBlocked()
            ->upcoming()
            ->whereDoesntHave('bookings', function ($q) {
                $q->whereIn('status', ['pending', 'approved']);
            })
            ->withinDailyLimit()
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'date' => $s->date->format('Y-m-d'),
                'date_display' => $s->date->isoFormat('Y年M月D日 (ddd)'),
                'start_time' => substr($s->start_time, 0, 5),
                'end_time' => substr($s->end_time, 0, 5),
            ]);

        return response()->json($schedules);
    }

    public function store(Request $request)
    {
        $bookingType = $request->input('booking_type', 'guest');

        $rules = [
            'booking_type' => ['required', 'in:member,guest'],
            'schedule_id' => ['required', 'exists:consultant_schedules,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($bookingType === 'member') {
            $rules['user_id'] = ['required', 'exists:users,id'];
        } else {
            $rules['guest_name'] = ['required', 'string', 'max:255'];
            $rules['guest_email'] = ['required', 'email', 'max:255'];
            $rules['guest_phone'] = ['required', 'string', 'max:20'];
            $rules['guest_referrer'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        $schedule = ConsultantSchedule::findOrFail($validated['schedule_id']);

        if (!$schedule->is_available || $schedule->calendar_blocked || $schedule->isBooked()) {
            return back()->withInput()->with('error', 'この時間枠は既に予約済みまたはブロックされています。');
        }

        $maxPerDay = (int) SystemSetting::get('max_bookings_per_day', 8);
        $dailyCount = Booking::where('consultant_id', $schedule->user_id)
            ->where('booking_date', $schedule->date)
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        if ($dailyCount >= $maxPerDay) {
            return back()->withInput()->with('error', 'このコンサルタントの予約枠は上限に達しています。');
        }

        // Real-time calendar conflict check
        $consultantProfile = $schedule->consultant->consultantProfile;
        if ($consultantProfile && $consultantProfile->isGoogleConnected()) {
            $conflictCalendarIds = $consultantProfile->getConflictCalendarIds();
            if (!empty($conflictCalendarIds)) {
                try {
                    $conflict = app(GoogleCalendarService::class)->checkSlotConflict(
                        $consultantProfile->google_refresh_token,
                        $conflictCalendarIds,
                        $schedule->date->format('Y-m-d'),
                        $schedule->start_time,
                        $schedule->end_time
                    );
                    if ($conflict) {
                        return back()->withInput()->with('error', 'この時間枠はコンサルタントの予定と重複しています。');
                    }
                } catch (\Exception $e) {
                    // API failure: allow booking (batch will catch conflicts later)
                }
            }
        }

        $bookingData = [
            'consultant_id' => $schedule->user_id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->date,
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'status' => 'approved',
            'notes' => $validated['notes'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? null,
        ];

        if ($bookingType === 'member') {
            $bookingData['user_id'] = $validated['user_id'];
            $bookingData['is_guest'] = false;
            $profile = $schedule->consultant?->consultantProfile;
            $bookingData['amount'] = $profile ? $profile->hourly_rate : 0;
        } else {
            $bookingData['user_id'] = null;
            $bookingData['is_guest'] = true;
            $bookingData['amount'] = 0;
            $bookingData['guest_name'] = $validated['guest_name'];
            $bookingData['guest_email'] = $validated['guest_email'];
            $bookingData['guest_phone'] = $validated['guest_phone'];
            $bookingData['guest_referrer'] = $validated['guest_referrer'] ?? null;
        }

        $booking = Booking::create($bookingData);

        try {
            $googleService = app(GoogleCalendarService::class);
            [$adminEventId, $adminCalendarId, $consultantEventId, $consultantCalendarId] = $googleService->syncCreateEvent($booking);
            $booking->update([
                'google_event_id' => $adminEventId,
                'admin_google_calendar_id' => $adminCalendarId,
                'consultant_google_event_id' => $consultantEventId,
                'consultant_google_calendar_id' => $consultantCalendarId,
            ]);
        } catch (\Exception $e) {
            // Google Calendar integration is optional
        }

        AuditLog::log('booking_created_by_admin', $booking);

        $notificationService = app(NotificationService::class);
        $notificationService->sendBookingConfirmation($booking);

        return redirect()->route('admin.bookings.index')
            ->with('success', '予約を作成しました。');
    }

    public function cancel(Request $request, Booking $booking)
    {
        if (!$booking->canCancel()) {
            return back()->with('error', 'この予約はキャンセルできません。');
        }

        $request->validate([
            'cancel_reason' => ['required', 'string', 'max:500'],
        ]);

        $booking->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->cancel_reason,
        ]);

        if ($booking->google_event_id || $booking->consultant_google_event_id) {
            try {
                $googleService = app(GoogleCalendarService::class);
                $googleService->syncDeleteEvent($booking);
                $booking->update(['google_event_id' => null, 'consultant_google_event_id' => null]);
            } catch (\Exception $e) {
                // Ignore Google Calendar errors
            }
        }

        AuditLog::log('booking_cancelled_by_admin', $booking);

        if (!$request->boolean('skip_notification')) {
            $notificationService = app(NotificationService::class);
            $notificationService->sendBookingCancelled($booking);
            return back()->with('success', '予約をキャンセルしました。予約者に通知が送信されました。');
        }

        return back()->with('success', '予約をキャンセルしました。（通知なし）');
    }

    public function updateConsultationRecord(Request $request, Booking $booking)
    {
        $request->validate([
            'consultation_result' => ['required', 'in:success,failure,pending'],
            'consultation_notes' => ['required', 'string'],
            'important_document_issued' => ['nullable', 'boolean'],
        ]);

        $data = [
            'consultation_result' => $request->consultation_result,
            'consultation_notes' => $request->consultation_notes,
            'important_document_issued' => $request->boolean('important_document_issued'),
        ];

        // 承認済みの予約は自動的に完了にする
        if ($booking->isApproved()) {
            $data['status'] = 'completed';
        }

        $booking->update($data);

        // 完了時にコンサルタントの実績をカウント
        if ($booking->status === 'completed' && $booking->wasChanged('status')) {
            $profile = $booking->consultant?->consultantProfile;
            if ($profile) {
                $profile->increment('total_bookings');
            }
            AuditLog::log('booking_completed', $booking);
        }

        AuditLog::log('consultation_record_updated', $booking);

        return back()->with('success', '相談記録を保存しました。');
    }

    public function updateNotes(Request $request, Booking $booking)
    {
        $request->validate([
            'admin_notes' => ['nullable', 'string'],
        ]);

        $booking->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'メモを保存しました。');
    }
}
