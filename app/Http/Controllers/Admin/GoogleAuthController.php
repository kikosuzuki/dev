<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SystemSetting;
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

        if (!$storedState || $storedState !== $request->input('state')) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'OAuth認証の状態が一致しません。もう一度お試しください。');
        }

        if ($request->has('error')) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'Googleアカウントの認証がキャンセルされました。');
        }

        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('admin.settings.index')
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
            return redirect()->route('admin.settings.index')
                ->with('error', 'トークンの取得に失敗しました。Google API認証情報を確認してください。');
        }

        $data = $response->json();
        $refreshToken = $data['refresh_token'] ?? null;
        $accessToken = $data['access_token'] ?? null;

        if (!$refreshToken) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'リフレッシュトークンが取得できませんでした。もう一度お試しください。');
        }

        // Get the connected account email
        $email = $this->getAccountEmail($accessToken);

        SystemSetting::set('google_refresh_token', $refreshToken, 'Googleリフレッシュトークン');
        if ($email) {
            SystemSetting::set('google_admin_email', $email, 'Google連携アカウント');
        }

        AuditLog::log('google_calendar_connected');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Googleアカウント（' . ($email ?? '不明') . '）を連携しました。');
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
