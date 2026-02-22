<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
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

        return view('admin.settings.index', compact('settings', 'auditLogs'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'require_booking_approval' => ['boolean'],
            'cancel_policy_hours' => ['required', 'integer', 'min:0'],
            'booking_slot_duration' => ['required', 'integer', 'min:15'],
            'max_bookings_per_day' => ['required', 'integer', 'min:1'],
            'schedule_per_page' => ['required', 'integer', 'min:10', 'max:200'],
            'schedule_disclosure_days' => ['required', 'integer', 'min:1'],
            'reminder_enabled' => ['boolean'],
            'reminder_day_before_hour' => ['required', 'integer', 'min:0', 'max:23'],
            'reminder_day_of_hour' => ['required', 'integer', 'min:0', 'max:23'],
            'reminder_minutes_before' => ['required', 'integer', 'min:1'],
            'default_reminder_message' => ['nullable', 'string', 'max:2000'],
            'line_channel_token' => ['nullable', 'string'],
            'line_channel_secret' => ['nullable', 'string'],
            'chatwork_api_token' => ['nullable', 'string'],
            'google_calendar_enabled' => ['boolean'],
            'guest_schedule_disclosure_days' => ['required', 'integer', 'min:1', 'max:365'],
            'guest_booking_confirmation_message' => ['nullable', 'string', 'max:2000'],
            'guest_reminder_message' => ['nullable', 'string', 'max:2000'],
            'guest_email_tpl_confirm_subject' => ['nullable', 'string', 'max:200'],
            'guest_email_tpl_confirm_body' => ['nullable', 'string', 'max:2000'],
            'guest_email_tpl_remind_subject' => ['nullable', 'string', 'max:200'],
            'guest_email_tpl_remind_body' => ['nullable', 'string', 'max:2000'],
            'guest_email_tpl_followup_subject' => ['nullable', 'string', 'max:200'],
            'guest_email_tpl_followup_body' => ['nullable', 'string', 'max:2000'],
            'guest_email_tpl_notice_subject' => ['nullable', 'string', 'max:200'],
            'guest_email_tpl_notice_body' => ['nullable', 'string', 'max:2000'],
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value);
        }

        AuditLog::log('settings_updated');

        return back()->with('success', 'システム設定を更新しました。');
    }
}
