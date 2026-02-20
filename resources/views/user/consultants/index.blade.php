@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">コンサルタント検索</h1>

    {{-- Search & Filter Form --}}
    <form method="GET" action="{{ route('user.consultants.index') }}" class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Keyword Search --}}
            <div>
                <label for="keyword" class="block text-sm font-medium text-gray-700 mb-1">キーワード</label>
                <input
                    type="text"
                    id="keyword"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="名前・専門分野で検索"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            {{-- Specialty Filter --}}
            <div>
                <label for="specialty" class="block text-sm font-medium text-gray-700 mb-1">専門分野</label>
                <select
                    id="specialty"
                    name="specialty"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">すべての専門分野</option>
                    @foreach($specialties ?? [] as $specialty)
                        <option value="{{ $specialty }}" {{ request('specialty') === $specialty ? 'selected' : '' }}>
                            {{ $specialty }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Sort --}}
            <div>
                <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">並び順</label>
                <select
                    id="sort"
                    name="sort"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>評価順</option>
                    <option value="reviews" {{ $sort === 'reviews' ? 'selected' : '' }}>レビュー数順</option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                検索
            </button>
        </div>
    </form>

    {{-- Consultant Card Grid --}}
    @if($consultants->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">該当するコンサルタントが見つかりませんでした。</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($consultants as $consultant)
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200 overflow-hidden">
                    <div class="p-6">
                        {{-- Photo / Avatar --}}
                        <div class="flex items-center mb-4">
                            @if($consultant->consultantProfile && $consultant->consultantProfile->photo)
                                <img
                                    src="{{ Storage::url($consultant->consultantProfile->photo) }}"
                                    alt="{{ $consultant->name }}"
                                    class="w-16 h-16 rounded-full object-cover"
                                >
                            @else
                                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-indigo-600 font-bold text-xl">{{ mb_substr($consultant->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $consultant->name }}</h3>
                                @if($consultant->consultantProfile)
                                    <p class="text-sm text-indigo-600 font-medium">{{ $consultant->consultantProfile->specialty }}</p>
                                @endif
                            </div>
                        </div>

                        @if($consultant->consultantProfile)
                            {{-- Rating Stars --}}
                            <div class="flex items-center mb-3">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= floor($consultant->consultantProfile->average_rating))
                                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @elseif($i - 0.5 <= $consultant->consultantProfile->average_rating)
                                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <defs>
                                                    <linearGradient id="half-star-{{ $consultant->id }}">
                                                        <stop offset="50%" stop-color="currentColor"/>
                                                        <stop offset="50%" stop-color="#D1D5DB"/>
                                                    </linearGradient>
                                                </defs>
                                                <path fill="url(#half-star-{{ $consultant->id }})" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endif
                                    @endfor
                                </div>
                                <span class="ml-2 text-sm text-gray-600">{{ number_format($consultant->consultantProfile->average_rating, 1) }}</span>
                                <span class="ml-1 text-sm text-gray-400">({{ $consultant->consultantProfile->total_reviews }}件)</span>
                            </div>

                            {{-- Details --}}
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span>経験年数: {{ $consultant->consultantProfile->experience_years }}年</span>
                                </div>
                            </div>
                        @endif

                        {{-- Profile Link --}}
                        <a
                            href="{{ route('user.consultants.show', $consultant) }}"
                            class="block w-full text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition"
                        >
                            プロフィールを見る
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $consultants->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
