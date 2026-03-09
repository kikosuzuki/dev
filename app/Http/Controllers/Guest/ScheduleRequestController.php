<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\ScheduleRequest;
use App\Models\SystemSetting;
use App\Services\ChatworkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScheduleRequestController extends Controller
{
    public function create(Request $request)
    {
        $intro = $request->get('intro');

        return view('guest.consultation.schedule-request', compact('intro'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:20'],
            'candidate_1' => ['required', 'string', 'max:255'],
            'candidate_2' => ['nullable', 'string', 'max:255'],
            'candidate_3' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $scheduleRequest = ScheduleRequest::create($validated);

        // Chatwork通知
        $this->sendChatworkNotification($scheduleRequest);

        return redirect()->route('consultation.schedule-request.complete')
            ->with('success', '日程調整のリクエストを送信しました。');
    }

    public function complete()
    {
        return view('guest.consultation.schedule-request-complete');
    }

    private function sendChatworkNotification(ScheduleRequest $scheduleRequest): void
    {
        if (SystemSetting::get('chatwork_enabled', '0') !== '1') {
            return;
        }

        $roomId = SystemSetting::get('chatwork_room_id', '');
        if (!$roomId) {
            return;
        }

        // カスタムメッセージがあればプレースホルダーを置換して使用
        $customMessage = SystemSetting::get('chatwork_schedule_request_message', '');
        if ($customMessage) {
            $message = str_replace(
                ['{name}', '{email}', '{phone}', '{candidate_1}', '{candidate_2}', '{candidate_3}', '{message}'],
                [
                    $scheduleRequest->guest_name,
                    $scheduleRequest->guest_email,
                    $scheduleRequest->guest_phone ?? '',
                    $scheduleRequest->candidate_1,
                    $scheduleRequest->candidate_2 ?? '',
                    $scheduleRequest->candidate_3 ?? '',
                    $scheduleRequest->message ?? '',
                ],
                $customMessage
            );
        } else {
            // デフォルトメッセージ
            $message = "[info][title]日程調整リクエスト[/title]"
                . "{$scheduleRequest->guest_name}様より日程調整のリクエストがありました。\n\n"
                . "■ メール: {$scheduleRequest->guest_email}\n"
                . ($scheduleRequest->guest_phone ? "■ 電話番号: {$scheduleRequest->guest_phone}\n" : '')
                . "\n【候補日時】\n"
                . "候補1: {$scheduleRequest->candidate_1}\n"
                . ($scheduleRequest->candidate_2 ? "候補2: {$scheduleRequest->candidate_2}\n" : '')
                . ($scheduleRequest->candidate_3 ? "候補3: {$scheduleRequest->candidate_3}\n" : '')
                . ($scheduleRequest->message ? "\n【ご相談内容】\n{$scheduleRequest->message}" : '')
                . "[/info]";
        }

        try {
            $chatworkService = new ChatworkService();
            $chatworkService->sendMessage($roomId, $message);
        } catch (\Exception $e) {
            Log::error('Schedule request Chatwork notification failed', [
                'schedule_request_id' => $scheduleRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
