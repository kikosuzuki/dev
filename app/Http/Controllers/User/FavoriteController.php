<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = auth()->user()->favorites()
            ->with('consultantProfile')
            ->paginate(12);

        return view('user.favorites.index', compact('favorites'));
    }

    public function toggle(User $consultant)
    {
        if (!$consultant->isConsultant()) {
            abort(404);
        }

        $user = auth()->user();
        $exists = $user->favorites()->where('consultant_id', $consultant->id)->exists();

        if ($exists) {
            $user->favorites()->detach($consultant->id);
            $message = 'お気に入りから削除しました。';
        } else {
            $user->favorites()->attach($consultant->id);
            $message = 'お気に入りに追加しました。';
        }

        if (request()->ajax()) {
            return response()->json(['favorited' => !$exists, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
