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
            'cancel_policy_hours' => ['required', 'integer', 'min:0'],
            'booking_slot_duration' => ['required', 'integer', 'min:15'],
            'max_bookings_per_day' => ['required', 'integer', 'min:1'],
            'schedule_disclosure_days' => ['required', 'integer', 'min:1'],
            'line_channel_token' => ['nullable', 'string'],
            'line_channel_secret' => ['nullable', 'string'],
            'chatwork_api_token' => ['nullable', 'string'],
            'google_calendar_enabled' => ['boolean'],
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value);
        }

        AuditLog::log('settings_updated');

        return back()->with('success', 'システム設定を更新しました。');
    }
}
