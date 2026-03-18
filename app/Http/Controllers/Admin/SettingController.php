<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\GuestMessageTemplate;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->keyBy('key');
        $auditLogs = AuditLog::with('user')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
        $templates = GuestMessageTemplate::ordered()->get();
        $templatesJson = $templates->map(function ($t) {
            return ['id' => $t->id, 'name' => $t->name, 'subject' => $t->subject ?? '', 'body' => $t->body ?? ''];
        })->values();

        return view('admin.settings.index', compact('settings', 'auditLogs', 'templates', 'templatesJson'));
    }

    public function updateBooking(Request $request)
    {
        $validated = $request->validate([
            'booking_acceptance_enabled' => ['boolean'],
            'cancel_policy_hours' => ['required', 'integer', 'min:0'],
            'hours_from_now' => ['required', 'integer', 'min:0'],
            'schedule_disclosure_days' => ['required', 'integer', 'min:1'],
            'booking_confirm_email_subject' => ['nullable', 'string', 'max:200'],
            'booking_confirm_email_body' => ['nullable', 'string', 'max:2000'],
            'booking_confirm_line_message' => ['nullable', 'string', 'max:2000'],
            'cancel_notification_email_subject' => ['nullable', 'string', 'max:200'],
            'cancel_notification_email_body' => ['nullable', 'string', 'max:2000'],
            'cancel_notification_line_message' => ['nullable', 'string', 'max:2000'],
            'max_bookings_per_day' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value);
        }

        AuditLog::log('booking_settings_updated');

        return redirect()->route('admin.settings.index', ['tab' => 'booking'])->with('success', '予約設定を更新しました。');
    }

    public function updateReminder(Request $request)
    {
        $validated = $request->validate([
            'reminder_day_before_enabled' => ['boolean'],
            'reminder_day_before_hour' => ['required', 'integer', 'min:0', 'max:23'],
            'reminder_day_before_email_subject' => ['nullable', 'string', 'max:200'],
            'reminder_day_before_email_body' => ['nullable', 'string', 'max:2000'],
            'reminder_day_before_line_message' => ['nullable', 'string', 'max:2000'],
            'reminder_day_of_enabled' => ['boolean'],
            'reminder_day_of_hour' => ['required', 'integer', 'min:0', 'max:23'],
            'reminder_day_of_email_subject' => ['nullable', 'string', 'max:200'],
            'reminder_day_of_email_body' => ['nullable', 'string', 'max:2000'],
            'reminder_day_of_line_message' => ['nullable', 'string', 'max:2000'],
            'reminder_minutes_before_enabled' => ['boolean'],
            'reminder_minutes_before' => ['required', 'integer', 'min:1'],
            'reminder_minutes_before_email_subject' => ['nullable', 'string', 'max:200'],
            'reminder_minutes_before_email_body' => ['nullable', 'string', 'max:2000'],
            'reminder_minutes_before_line_message' => ['nullable', 'string', 'max:2000'],
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value);
        }

        AuditLog::log('reminder_settings_updated');

        return redirect()->route('admin.settings.index', ['tab' => 'reminder'])->with('success', 'リマインダー設定を更新しました。');
    }

    public function updateTemplates(Request $request)
    {
        $validated = $request->validate([
            'templates' => ['required', 'array', 'max:10'],
            'templates.*.id' => ['nullable', 'integer', 'exists:guest_message_templates,id'],
            'templates.*.name' => ['required', 'string', 'max:100'],
            'templates.*.subject' => ['nullable', 'string', 'max:200'],
            'templates.*.body' => ['nullable', 'string', 'max:2000'],
        ]);

        $submittedIds = collect($validated['templates'])
            ->pluck('id')
            ->filter()
            ->toArray();

        GuestMessageTemplate::whereNotIn('id', $submittedIds)->delete();

        foreach ($validated['templates'] as $index => $data) {
            if (!empty($data['id'])) {
                GuestMessageTemplate::where('id', $data['id'])->update([
                    'name' => $data['name'],
                    'subject' => $data['subject'] ?? null,
                    'body' => $data['body'] ?? null,
                    'sort_order' => $index,
                ]);
            } else {
                GuestMessageTemplate::create([
                    'name' => $data['name'],
                    'subject' => $data['subject'] ?? null,
                    'body' => $data['body'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        }

        AuditLog::log('guest_templates_updated');

        return redirect()->route('admin.settings.index', ['tab' => 'templates'])->with('success', 'ゲスト向けメッセージテンプレートを更新しました。');
    }

    public function updateIntegration(Request $request)
    {
        $validated = $request->validate([
            'line_channel_token' => ['nullable', 'string'],
            'line_channel_secret' => ['nullable', 'string'],
            'line_enabled' => ['boolean'],
            'chatwork_api_token' => ['nullable', 'string'],
            'chatwork_room_id' => ['nullable', 'string', 'max:50'],
            'chatwork_enabled' => ['boolean'],
            'chatwork_booking_confirm_enabled' => ['boolean'],
            'chatwork_booking_confirm_message' => ['nullable', 'string', 'max:2000'],
            'chatwork_cancel_notification_enabled' => ['boolean'],
            'chatwork_cancel_notification_message' => ['nullable', 'string', 'max:2000'],
            'chatwork_morning_notification_enabled' => ['boolean'],
            'chatwork_morning_notification_message' => ['nullable', 'string', 'max:2000'],
            'chatwork_schedule_request_enabled' => ['boolean'],
            'chatwork_schedule_request_message' => ['nullable', 'string', 'max:2000'],
            'chatwork_reminder_day_before_enabled' => ['boolean'],
            'chatwork_reminder_day_before_message' => ['nullable', 'string', 'max:2000'],
            'chatwork_reminder_before_start_enabled' => ['boolean'],
            'chatwork_reminder_before_start_message' => ['nullable', 'string', 'max:2000'],
            'google_calendar_enabled' => ['boolean'],
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value);
        }

        AuditLog::log('integration_settings_updated');

        return redirect()->route('admin.settings.index', ['tab' => 'integration'])->with('success', '連携設定を更新しました。');
    }
}
