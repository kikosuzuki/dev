<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ConsultantProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManageController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->get('role', 'all');
        $query = User::query();

        if ($role !== 'all') {
            $query->where('role', $role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.users.index', compact('users', 'role'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['user', 'consultant', 'admin'])],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if ($validated['role'] === 'consultant') {
            ConsultantProfile::create([
                'user_id' => $user->id,
                'specialty' => $request->get('specialty', '未設定'),
            ]);
        }

        AuditLog::log('user_created', $user);

        return redirect()->route('admin.users.index')->with('success', 'ユーザーを登録しました。');
    }

    public function edit(User $user)
    {
        $user->load('consultantProfile');
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['user', 'consultant', 'admin'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $oldValues = $user->toArray();
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => ['string', 'min:8']]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        AuditLog::log('user_updated', $user, $oldValues, $user->toArray());

        return redirect()->route('admin.users.index')->with('success', 'ユーザー情報を更新しました。');
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? '有効化' : '無効化';
        AuditLog::log("user_{$status}", $user);

        return back()->with('success', "ユーザーを{$status}しました。");
    }
}
