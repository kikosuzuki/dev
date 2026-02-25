<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->isConsultant()) {
                return redirect()->route('consultant.dashboard');
            }
            return redirect()->route('user.dashboard');
        }

        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => 'user',
        ]);

        // 登録完了メール送信
        try {
            Mail::raw(
                "{$user->name}様\n\n"
                . "YCSコンサルタント予約システムへのご登録ありがとうございます。\n\n"
                . "■ ログインメールアドレス: {$user->email}\n"
                . "■ ログインURL: " . route('login') . "\n\n"
                . "パスワードは登録時にご自身で設定されたものをご使用ください。\n"
                . "パスワードをお忘れの場合はログイン画面の「パスワードをお忘れですか？」からリセットできます。",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('【登録完了】YCSコンサルタント予約システム');
                }
            );
        } catch (\Exception $e) {
            Log::error('Registration email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        Auth::login($user);

        return redirect()->route('user.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
