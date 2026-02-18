<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->status !== 'completed') {
            return back()->with('error', '完了済みの予約のみレビューできます。');
        }

        if ($booking->review) {
            return back()->with('error', 'この予約には既にレビューを投稿済みです。');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        Review::create([
            'user_id' => auth()->id(),
            'consultant_id' => $booking->consultant_id,
            'booking_id' => $booking->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        $profile = $booking->consultant->consultantProfile;
        if ($profile) {
            $profile->updateRating();
        }

        return back()->with('success', 'レビューを投稿しました。');
    }
}
