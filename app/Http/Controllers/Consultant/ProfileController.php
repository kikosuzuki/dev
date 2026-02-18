<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Models\ConsultantProfile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $profile = $user->consultantProfile ?? new ConsultantProfile();
        return view('consultant.profile.edit', compact('user', 'profile'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'specialty' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'hourly_rate' => ['required', 'integer', 'min:0'],
            'experience_years' => ['required', 'integer', 'min:0'],
            'qualifications' => ['nullable', 'string'],
            'languages' => ['nullable', 'string'],
            'auto_approve' => ['boolean'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = auth()->user();

        $profileData = [
            'specialty' => $validated['specialty'],
            'bio' => $validated['bio'],
            'hourly_rate' => $validated['hourly_rate'],
            'experience_years' => $validated['experience_years'],
            'qualifications' => $validated['qualifications'] ? array_map('trim', explode(',', $validated['qualifications'])) : [],
            'languages' => $validated['languages'] ? array_map('trim', explode(',', $validated['languages'])) : [],
            'auto_approve' => $request->boolean('auto_approve'),
        ];

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('consultant_photos', 'public');
            $profileData['photo'] = $path;
        }

        $user->consultantProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        // Also update user info
        $user->update($request->only(['name', 'phone', 'notification_channel']));

        return back()->with('success', 'プロフィールを更新しました。');
    }
}
