<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
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

        if ($role === 'guest') {
            return $this->guestIndex($request);
        }

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

    private function guestIndex(Request $request)
    {
        $role = 'guest';
        $query = Booking::where('is_guest', true)
            ->with('consultant.consultantProfile');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                    ->orWhere('guest_email', 'like', "%{$search}%")
                    ->orWhere('guest_phone', 'like', "%{$search}%")
                    ->orWhere('guest_referrer', 'like', "%{$search}%");
            });
        }

        $guests = $query->orderByDesc('booking_date')->paginate(20);

        return view('admin.users.index', compact('guests', 'role'));
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
            'chatwork_id' => ['nullable', 'string', 'max:100'],
            'chatwork_room_id' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'admin_notes' => ['nullable', 'string'],
            'user_type' => ['required', Rule::in(['member', 'consultation'])],
        ]);

        $oldValues = $user->toArray();
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'chatwork_id' => $validated['chatwork_id'] ?? null,
            'chatwork_room_id' => $validated['chatwork_room_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'admin_notes' => $validated['admin_notes'] ?? null,
            'user_type' => $validated['user_type'],
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

    public function editGuest(Booking $booking)
    {
        if (!$booking->isGuest()) {
            abort(404);
        }

        $booking->load('consultant.consultantProfile');

        return view('admin.users.guest-edit', compact('booking'));
    }

    public function updateGuest(Request $request, Booking $booking)
    {
        if (!$booking->isGuest()) {
            abort(404);
        }

        $validated = $request->validate([
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:20'],
            'guest_referrer' => ['nullable', 'string', 'max:255'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $oldValues = $booking->only(['guest_name', 'guest_email', 'guest_phone', 'guest_referrer', 'admin_notes']);
        $booking->update($validated);

        AuditLog::log('guest_booking_updated', $booking, $oldValues, $booking->only(['guest_name', 'guest_email', 'guest_phone', 'guest_referrer', 'admin_notes']));

        return redirect()->route('admin.users.index', ['role' => 'guest'])->with('success', 'ゲスト相談者情報を更新しました。');
    }
}
