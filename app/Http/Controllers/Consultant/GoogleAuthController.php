<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ConsultantSchedule;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect_uri');

        if (!$clientId || !$redirectUri) {
            return back()->with('error', 'Google API認証情報が設定されていません。管理者にお問い合わせください。');
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);
        $request->session()->put('google_oauth_type', 'consultant');

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/userinfo.email',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return redirect("https://accounts.google.com/o/oauth2/v2/auth?{$params}");
    }

    public function disconnect()
    {
        $user = auth()->user();
        $profile = $user->consultantProfile;

        if ($profile) {
            $profile->update([
                'google_refresh_token' => null,
                'google_calendar_email' => null,
                'google_calendar_id' => null,
            ]);
        }

        AuditLog::log('consultant_google_calendar_disconnected');

        return back()->with('success', 'Googleアカウントの連携を解除しました。');
    }

    public function calendars()
    {
        $user = auth()->user();
        $profile = $user->consultantProfile;

        if (!$profile || !$profile->isGoogleConnected()) {
            return response()->json(['calendars' => [], 'error' => 'Google未連携'], 400);
        }

        $googleService = app(GoogleCalendarService::class);
        $calendars = $googleService->listCalendars($profile->google_refresh_token);

        return response()->json([
            'calendars' => $calendars,
            'selected' => $profile->google_calendar_id ?: 'primary',
        ]);
    }

    public function updateCalendar(Request $request)
    {
        $request->validate([
            'google_calendar_id' => ['required', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        $profile = $user->consultantProfile;

        if (!$profile || !$profile->isGoogleConnected()) {
            return response()->json(['error' => 'Googleアカウントが連携されていません。'], 400);
        }

        $profile->update([
            'google_calendar_id' => $request->google_calendar_id,
        ]);

        return response()->json(['success' => true]);
    }

    public function allCalendars()
    {
        $user = auth()->user();
        $profile = $user->consultantProfile;

        if (!$profile || !$profile->isGoogleConnected()) {
            return response()->json(['calendars' => [], 'error' => 'Google未連携'], 400);
        }

        $googleService = app(GoogleCalendarService::class);
        $calendars = $googleService->listAllCalendars($profile->google_refresh_token);

        return response()->json([
            'calendars' => $calendars,
            'selected' => $profile->getConflictCalendarIds(),
        ]);
    }

    public function updateConflictCalendars(Request $request)
    {
        $request->validate([
            'google_conflict_calendar_ids' => ['required', 'array'],
            'google_conflict_calendar_ids.*' => ['string', 'max:255'],
        ]);

        $user = auth()->user();
        $profile = $user->consultantProfile;

        if (!$profile || !$profile->isGoogleConnected()) {
            return response()->json(['error' => 'Googleアカウントが連携されていません。'], 400);
        }

        $profile->update([
            'google_conflict_calendar_ids' => $request->google_conflict_calendar_ids,
        ]);

        return response()->json(['success' => true]);
    }

    public function updateAvailableSlotSync(Request $request)
    {
        $request->validate([
            'sync_available_slots' => ['required', 'boolean'],
        ]);

        $user = auth()->user();
        $profile = $user->consultantProfile;

        if (!$profile || !$profile->isGoogleConnected()) {
            return response()->json(['success' => false, 'message' => 'Googleアカウントが連携されていません。'], 400);
        }

        $newValue = $request->boolean('sync_available_slots');
        $oldValue = (bool) $profile->sync_available_slots;

        $profile->update(['sync_available_slots' => $newValue]);

        $googleService = app(GoogleCalendarService::class);

        if ($newValue && !$oldValue) {
            // OFF → ON: bulk create events for existing available slots
            $schedules = ConsultantSchedule::where('user_id', $user->id)
                ->where('is_available', true)
                ->where('calendar_blocked', false)
                ->whereNull('google_event_id')
                ->where('date', '>=', now()->toDateString())
                ->whereDoesntHave('bookings', function ($q) {
                    $q->whereIn('status', ['pending', 'approved']);
                })
                ->get();

            $created = 0;
            foreach ($schedules as $schedule) {
                try {
                    $eventId = $googleService->createAvailableSlotEvent($schedule);
                    if ($eventId) {
                        $created++;
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to create available slot event during bulk sync', [
                        'schedule_id' => $schedule->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "空き枠同期をONにしました。{$created}件の空き枠をカレンダーに登録しました。",
            ]);
        } elseif (!$newValue && $oldValue) {
            // ON → OFF: bulk delete events for existing slots
            $schedules = ConsultantSchedule::where('user_id', $user->id)
                ->whereNotNull('google_event_id')
                ->get();

            $deleted = 0;
            foreach ($schedules as $schedule) {
                try {
                    $googleService->deleteAvailableSlotEvent($schedule);
                    $deleted++;
                } catch (\Exception $e) {
                    Log::warning('Failed to delete available slot event during bulk sync', [
                        'schedule_id' => $schedule->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "空き枠同期をOFFにしました。{$deleted}件のカレンダーイベントを削除しました。",
            ]);
        }

        return response()->json(['success' => true, 'message' => '設定を保存しました。']);
    }
}
