@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">予約一覧</h1>
        <p class="mt-1 text-gray-600">全ての予約履歴を確認できます。</p>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-300 text-green-700 rounded-md p-4">
            <p class="text-sm">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-red-50 border border-red-300 text-red-700 rounded-md p-4">
            <p class="text-sm">{{ session('error') }}</p>
        </div>
    @endif

    {{-- ステータスフィルタータブ --}}
    <div class="mb-6 border-b border-gray-200">
        <nav class="flex space-x-6 -mb-px">
            <a href="{{ route('user.bookings.index', ['status' => 'all']) }}"
                class="py-3 px-1 border-b-2 text-sm font-medium transition {{ $status === 'all' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                全て
            </a>
            <a href="{{ route('user.bookings.index', ['status' => 'approved']) }}"
                class="py-3 px-1 border-b-2 text-sm font-medium transition {{ $status === 'approved' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                確定済み
            </a>
            <a href="{{ route('user.bookings.index', ['status' => 'completed']) }}"
                class="py-3 px-1 border-b-2 text-sm font-medium transition {{ $status === 'completed' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                完了
            </a>
            <a href="{{ route('user.bookings.index', ['status' => 'cancelled']) }}"
                class="py-3 px-1 border-b-2 text-sm font-medium transition {{ $status === 'cancelled' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                キャンセル済み
            </a>
        </nav>
    </div>

    {{-- 予約リスト --}}
    @if ($bookings->isEmpty())
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="mt-4 text-gray-500">予約が見つかりません。</p>
            <a href="{{ route('user.consultants.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-sm font-medium">
                コンサルタントを探す
            </a>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="divide-y divide-gray-200">
                @foreach ($bookings as $booking)
                    <div class="p-6 hover:bg-gray-50 transition">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-base font-semibold text-gray-900">
                                        {{ $booking->consultant->name }}
                                    </h3>
                                    @switch($booking->status)
                                        @case('approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">確定</span>
                                            @break
                                        @case('completed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">完了</span>
                                            @break
                                        @case('cancelled')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">キャンセル済み</span>
                                            @break
                                    @endswitch
                                </div>
                                @if ($booking->consultant->consultantProfile)
                                    <p class="text-sm text-gray-500 mb-1">{{ $booking->consultant->consultantProfile->specialty }}</p>
                                @endif
                                <div class="flex items-center flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $booking->booking_date->format('Y/m/d') }}
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ \Illuminate\Support\Str::substr($booking->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($booking->end_time, 0, 5) }}
                                    </span>
                                    <span class="text-gray-400">|</span>
                                    <span class="text-gray-400 text-xs">予約日時: {{ $booking->created_at->format('Y/m/d H:i') }}</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="{{ route('user.bookings.show', $booking) }}"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition text-sm font-medium">
                                    詳細を見る
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ページネーション --}}
        <div class="mt-6">
            {{ $bookings->appends(['status' => $status])->links() }}
        </div>
    @endif
</div>
@endsection
