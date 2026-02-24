<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect_uri');

        if (!$clientId || !$redirectUri) {
            return back()->with('error', 'Google API認証情報が設定されていません。.envファイルのGOOGLE_CLIENT_ID、GOOGLE_REDIRECT_URIを確認してください。');
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);
        $request->session()->put('google_oauth_type', 'admin');

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

    public function callback(Request $request)
    {
        $storedState = $request->session()->pull('google_oauth_state');
        $oauthType = $request->session()->pull('google_oauth_type', 'admin');

        $errorRoute = $oauthType === 'consultant' ? 'consultant.profile.edit' : 'admin.settings.index';

        if (!$storedState || $storedState !== $request->input('state')) {
            return redirect()->route($errorRoute)
                ->with('error', 'OAuth認証の状態が一致しません。もう一度お試しください。');
        }

        if ($request->has('error')) {
            return redirect()->route($errorRoute)
                ->with('error', 'Googleアカウントの認証がキャンセルされました。');
        }

        $code = $request->input('code');
        if (!$code) {
            return redirect()->route($errorRoute)
                ->with('error', '認証コードが取得できませんでした。');
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google.redirect_uri'),
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful()) {
            return redirect()->route($errorRoute)
                ->with('error', 'トークンの取得に失敗しました。Google API認証情報を確認してください。');
        }

        $data = $response->json();
        $refreshToken = $data['refresh_token'] ?? null;
        $accessToken = $data['access_token'] ?? null;

        if (!$refreshToken) {
            return redirect()->route($errorRoute)
                ->with('error', 'リフレッシュトークンが取得できませんでした。もう一度お試しください。');
        }

        $email = $this->getAccountEmail($accessToken);

        if ($oauthType === 'consultant') {
            return $this->handleConsultantCallback($refreshToken, $email);
        }

        return $this->handleAdminCallback($refreshToken, $email);
    }

    private function handleAdminCallback(string $refreshToken, ?string $email)
    {
        SystemSetting::set('google_refresh_token', $refreshToken, 'Googleリフレッシュトークン');
        if ($email) {
            SystemSetting::set('google_admin_email', $email, 'Google連携アカウント');
        }

        AuditLog::log('google_calendar_connected');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Googleアカウント（' . ($email ?? '不明') . '）を連携しました。');
    }

    private function handleConsultantCallback(string $refreshToken, ?string $email)
    {
        $user = auth()->user();
        $user->consultantProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'google_refresh_token' => $refreshToken,
                'google_calendar_email' => $email,
                'google_calendar_id' => null,
            ]
        );

        AuditLog::log('consultant_google_calendar_connected');

        return redirect()->route('consultant.profile.edit')
            ->with('success', 'Googleアカウント（' . ($email ?? '不明') . '）を連携しました。');
    }

    public function calendars()
    {
        $refreshToken = SystemSetting::get('google_refresh_token');
        if (!$refreshToken) {
            return response()->json(['calendars' => [], 'error' => 'Google未連携'], 400);
        }

        $googleService = app(GoogleCalendarService::class);
        $calendars = $googleService->listCalendars($refreshToken);

        return response()->json([
            'calendars' => $calendars,
            'selected' => SystemSetting::get('google_calendar_id', 'primary'),
        ]);
    }

    public function updateCalendar(Request $request)
    {
        $request->validate([
            'google_calendar_id' => ['required', 'string', 'max:255'],
        ]);

        SystemSetting::set('google_calendar_id', $request->google_calendar_id, '管理者Googleカレンダー同期先');

        return response()->json(['success' => true]);
    }

    public function disconnect()
    {
        SystemSetting::set('google_refresh_token', '', 'Googleリフレッシュトークン');
        SystemSetting::set('google_admin_email', '', 'Google連携アカウント');

        AuditLog::log('google_calendar_disconnected');

        return back()->with('success', 'Googleアカウントの連携を解除しました。');
    }

    private function getAccountEmail(?string $accessToken): ?string
    {
        if (!$accessToken) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if ($response->successful()) {
                return $response->json('email');
            }
        } catch (\Exception $e) {
            // Ignore - email is optional info
        }

        return null;
    }
}
