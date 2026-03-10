@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">管理ダッシュボード</h1>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-300 text-green-700 rounded-md p-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-300 text-red-700 rounded-md p-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">総ユーザー数</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_users']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">コンサルタント数</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_consultants']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">総予約数</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_bookings']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">確定済み予約</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['active_bookings']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">今月の予約数</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['this_month_bookings']) }}</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Consultation Result Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">成約</p>
                    <p class="text-2xl font-semibold text-green-600">{{ number_format($stats['consultation_success']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">不成約</p>
                    <p class="text-2xl font-semibold text-red-600">{{ number_format($stats['consultation_failure']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">検討中</p>
                    <p class="text-2xl font-semibold text-yellow-600">{{ number_format($stats['consultation_pending']) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Bookings / Revenue Chart Placeholder --}}
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">月別予約数</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">月</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">予約数</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($monthlyBookings as $monthly)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $monthly->month }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($monthly->count) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">データがありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Bookings --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">予約一覧</h2>
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        {{ $recentBookings->total() }}件
                    </span>
                    <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                        予約一覧で操作 &rarr;
                    </a>
                </div>
            </div>
            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.dashboard') }}">
                <div class="flex flex-wrap items-end gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">種別</label>
                        <select name="booking_type" class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" style="padding: 0.375rem 1.75rem 0.375rem 0.5rem;">
                            <option value="">全て</option>
                            <option value="member" {{ ($booking_type ?? '') == 'member' ? 'selected' : '' }}>会員</option>
                            <option value="guest" {{ ($booking_type ?? '') == 'guest' ? 'selected' : '' }}>個別相談</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">コンサルタント</label>
                        <select name="consultant_id" class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" style="padding: 0.375rem 1.75rem 0.375rem 0.5rem;">
                            <option value="">全員</option>
                            @foreach($consultants as $c)
                                <option value="{{ $c->id }}" {{ ($consultant_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ステータス</label>
                        <select name="status" class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" style="padding: 0.375rem 1.75rem 0.375rem 0.5rem;">
                            <option value="all" {{ ($status ?? '') == 'all' || !($status ?? '') ? 'selected' : '' }}>全て</option>
                            <option value="approved" {{ ($status ?? '') == 'approved' ? 'selected' : '' }}>確定</option>
                            <option value="completed" {{ ($status ?? '') == 'completed' ? 'selected' : '' }}>完了</option>
                            <option value="cancelled" {{ ($status ?? '') == 'cancelled' ? 'selected' : '' }}>キャンセル</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">相談結果</label>
                        <select name="consultation_result" class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" style="padding: 0.375rem 1.75rem 0.375rem 0.5rem;">
                            <option value="all" {{ ($consultation_result ?? '') == 'all' || !($consultation_result ?? '') ? 'selected' : '' }}>全て</option>
                            <option value="success" {{ ($consultation_result ?? '') == 'success' ? 'selected' : '' }}>成約</option>
                            <option value="failure" {{ ($consultation_result ?? '') == 'failure' ? 'selected' : '' }}>不成約</option>
                            <option value="pending" {{ ($consultation_result ?? '') == 'pending' ? 'selected' : '' }}>検討中</option>
                            <option value="unrecorded" {{ ($consultation_result ?? '') == 'unrecorded' ? 'selected' : '' }}>未記録</option>
                        </select>
                    </div>
                </div>
                {{-- Search --}}
                <div class="flex items-center space-x-4">
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ $search ?? '' }}"
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="名前またはメールで検索...">
                        </div>
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                        検索
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 border border-gray-300 transition">
                        リセット
                    </a>
                </div>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">予約ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">種別</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">予約者</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">コンサルタント</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">相談日時</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">相談結果</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentBookings as $booking)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $booking->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($booking->isGuest())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">個別相談</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">会員</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $booking->bookerName() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $booking->consultant->name ?? '不明' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $booking->booking_date->format('Y/m/d') }} {{ $booking->start_time }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @switch($booking->status)
                                    @case('approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">確定</span>
                                        @break
                                    @case('completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">完了</span>
                                        @break
                                    @case('cancelled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">キャンセル</span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $booking->status }}</span>
                                @endswitch
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($booking->consultation_result === 'success')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">成約</span>
                                @elseif($booking->consultation_result === 'failure')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">不成約</span>
                                @elseif($booking->consultation_result === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">検討中</span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">予約データがありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recentBookings->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $recentBookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
