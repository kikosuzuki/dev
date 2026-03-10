<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ConsultantBrowseController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'consultant')
            ->where('is_active', true)
            ->whereHas('consultantProfile')
            ->with('consultantProfile');

        if ($request->filled('specialty')) {
            $query->whereHas('consultantProfile', function ($q) use ($request) {
                $q->where('specialty', 'like', '%' . $request->specialty . '%');
            });
        }

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhereHas('consultantProfile', function ($q) use ($keyword) {
                        $q->where('bio', 'like', "%{$keyword}%")
                            ->orWhere('specialty', 'like', "%{$keyword}%");
                    });
            });
        }

        $sort = $request->get('sort', 'rating');
        switch ($sort) {
            case 'price_low':
                $query->orderBy(
                    \App\Models\ConsultantProfile::select('hourly_rate')
                        ->whereColumn('consultant_profiles.user_id', 'users.id')
                        ->limit(1),
                    'asc'
                );
                break;
            case 'price_high':
                $query->orderBy(
                    \App\Models\ConsultantProfile::select('hourly_rate')
                        ->whereColumn('consultant_profiles.user_id', 'users.id')
                        ->limit(1),
                    'desc'
                );
                break;
            case 'reviews':
                $query->orderBy(
                    \App\Models\ConsultantProfile::select('total_reviews')
                        ->whereColumn('consultant_profiles.user_id', 'users.id')
                        ->limit(1),
                    'desc'
                );
                break;
            default:
                $query->orderBy(
                    \App\Models\ConsultantProfile::select('average_rating')
                        ->whereColumn('consultant_profiles.user_id', 'users.id')
                        ->limit(1),
                    'desc'
                );
                break;
        }

        $consultants = $query->paginate(12);

        return view('user.consultants.index', compact('consultants', 'sort'));
    }

    public function show(User $consultant)
    {
        if (!$consultant->isConsultant()) {
            abort(404);
        }

        $consultant->load(['consultantProfile', 'receivedReviews.user']);

        $upcomingSchedules = $consultant->schedules()
            ->upcoming()
            ->where('is_available', true)
            ->notCalendarBlocked()
            ->whereDoesntHave('bookings', function ($q) {
                $q->whereIn('status', ['pending', 'approved']);
            })
            ->withinDailyLimit()
            ->orderBy('date')
            ->orderBy('start_time')
            ->limit(20)
            ->get();

        $isFavorited = false;
        if (auth()->check()) {
            $isFavorited = auth()->user()->favorites()->where('consultant_id', $consultant->id)->exists();
        }

        $reviews = $consultant->receivedReviews()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(5);

        return view('user.consultants.show', compact('consultant', 'upcomingSchedules', 'isFavorited', 'reviews'));
    }
}
