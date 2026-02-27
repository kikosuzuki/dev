@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Back Link --}}
    <a href="{{ route('user.consultants.index') }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        コンサルタント一覧に戻る
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Main Profile Section --}}
        <div class="lg:col-span-2 space-y-8">
            {{-- Profile Card --}}
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                    {{-- Photo --}}
                    @if($consultant->consultantProfile && $consultant->consultantProfile->photo)
                        <img
                            src="{{ Storage::disk('public')->url($consultant->consultantProfile->photo) }}"
                            alt="{{ $consultant->name }}"
                            class="w-24 h-24 rounded-full object-cover flex-shrink-0"
                        >
                    @else
                        <div class="w-24 h-24 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-indigo-600 font-bold text-3xl">{{ mb_substr($consultant->name, 0, 1) }}</span>
                        </div>
                    @endif

                    <div class="flex-1">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ $consultant->name }}</h1>
                                @if($consultant->consultantProfile)
                                    <p class="text-indigo-600 font-medium mt-1">{{ $consultant->consultantProfile->specialty }}</p>
                                @endif
                            </div>

                            {{-- Favorite Toggle Button (Alpine.js) --}}
                            <div x-data="{ favorited: {{ $isFavorited ? 'true' : 'false' }}, loading: false }">
                                <button
                                    @click="
                                        if (loading) return;
                                        loading = true;
                                        fetch('{{ route('user.favorites.toggle', $consultant) }}', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                                'Accept': 'application/json',
                                                'Content-Type': 'application/json'
                                            }
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            favorited = data.favorited;
                                            loading = false;
                                        })
                                        .catch(() => { loading = false; });
                                    "
                                    class="inline-flex items-center px-4 py-2 border rounded-md text-sm font-medium transition"
                                    :class="favorited ? 'border-red-300 text-red-600 bg-red-50 hover:bg-red-100' : 'border-gray-300 text-gray-600 bg-white hover:bg-gray-50'"
                                    :disabled="loading"
                                >
                                    <svg
                                        class="w-5 h-5 mr-1"
                                        :class="favorited ? 'text-red-500' : 'text-gray-400'"
                                        :fill="favorited ? 'currentColor' : 'none'"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    <span x-text="favorited ? 'お気に入り済み' : 'お気に入りに追加'"></span>
                                </button>
                            </div>
                        </div>

                        @if($consultant->consultantProfile)
                            {{-- Rating --}}
                            <div class="flex items-center mt-3">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= floor($consultant->consultantProfile->average_rating))
                                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @elseif($i - 0.5 <= $consultant->consultantProfile->average_rating)
                                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <defs>
                                                    <linearGradient id="half-star-show-{{ $i }}">
                                                        <stop offset="50%" stop-color="currentColor"/>
                                                        <stop offset="50%" stop-color="#D1D5DB"/>
                                                    </linearGradient>
                                                </defs>
                                                <path fill="url(#half-star-show-{{ $i }})" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endif
                                    @endfor
                                </div>
                                <span class="ml-2 text-sm text-gray-600">{{ number_format($consultant->consultantProfile->average_rating, 1) }}</span>
                                <span class="ml-1 text-sm text-gray-400">({{ $consultant->consultantProfile->total_reviews }}件のレビュー)</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if($consultant->consultantProfile)
                    {{-- Bio --}}
                    <div class="mt-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">自己紹介</h2>
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultant->consultantProfile->bio }}</p>
                    </div>

                    {{-- Details Grid --}}
                    <div class="mt-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-500">経験年数</p>
                            <p class="text-xl font-bold text-gray-900">{{ $consultant->consultantProfile->experience_years }}<span class="text-sm font-normal text-gray-500">年</span></p>
                        </div>
                    </div>

                    {{-- Qualifications --}}
                    @if(!empty($consultant->consultantProfile->qualifications))
                        <div class="mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-2">資格・認定</h2>
                            <ul class="space-y-1">
                                @foreach($consultant->consultantProfile->qualifications as $qualification)
                                    <li class="flex items-center text-gray-700">
                                        <svg class="w-4 h-4 mr-2 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{ $qualification }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Languages --}}
                    @if(!empty($consultant->consultantProfile->languages))
                        <div class="mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-2">対応言語</h2>
                            <div class="flex flex-wrap gap-2">
                                @foreach($consultant->consultantProfile->languages as $language)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                        {{ $language }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            {{-- Reviews Section --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">レビュー</h2>

                @if($reviews->isEmpty())
                    <p class="text-gray-500">まだレビューはありません。</p>
                @else
                    <div class="space-y-6">
                        @foreach($reviews as $review)
                            <div class="border-b border-gray-200 pb-6 last:border-b-0 last:pb-0">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <span class="font-medium text-gray-900">{{ $review->user->name }}</span>
                                        <div class="flex items-center ml-3">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rating)
                                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-500">{{ $review->created_at->format('Y/m/d') }}</span>
                                </div>
                                @if($review->comment)
                                    <p class="text-gray-700">{{ $review->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Reviews Pagination --}}
                    <div class="mt-6">
                        {{ $reviews->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar: Available Schedules --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">予約可能なスケジュール</h2>

                @if($upcomingSchedules->isEmpty())
                    <p class="text-gray-500 text-sm">現在予約可能なスケジュールはありません。</p>
                @else
                    <div class="space-y-3">
                        @foreach($upcomingSchedules as $schedule)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $schedule->date->format('Y/m/d') }}</p>
                                        <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</p>
                                    </div>
                                    <a
                                        href="{{ route('user.bookings.create', $schedule) }}"
                                        class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition"
                                    >
                                        予約する
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
