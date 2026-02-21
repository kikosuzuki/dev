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
            'experience_years' => ['required', 'integer', 'min:0'],
            'qualifications' => ['nullable', 'string'],
            'languages' => ['nullable', 'string'],
            'auto_approve' => ['boolean'],
            'meeting_url' => ['nullable', 'url', 'max:500'],
            'reminder_message' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = auth()->user();

        $profileData = [
            'specialty' => $validated['specialty'],
            'bio' => $validated['bio'],
            'experience_years' => $validated['experience_years'],
            'qualifications' => $validated['qualifications'] ? array_map('trim', explode(',', $validated['qualifications'])) : [],
            'languages' => $validated['languages'] ? array_map('trim', explode(',', $validated['languages'])) : [],
            'auto_approve' => $request->boolean('auto_approve'),
            'meeting_url' => $validated['meeting_url'],
            'reminder_message' => $validated['reminder_message'],
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
        $user->update([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'notify_email' => $request->boolean('notify_email'),
            'notify_line' => $request->boolean('notify_line'),
        ]);

        return back()->with('success', 'プロフィールを更新しました。');
    }
}
