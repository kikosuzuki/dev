@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">予約一覧</h1>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4">
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">期間</label>
                    <select name="period" class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" style="padding: 0.5rem 2rem 0.5rem 0.75rem;">
                        @foreach($periods as $key => $label)
                            <option value="{{ $key }}" {{ $period == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">コンサルタント</label>
                    <select name="consultant_id" class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" style="padding: 0.5rem 2rem 0.5rem 0.75rem;">
                        <option value="">全員</option>
                        @foreach($consultants as $c)
                            <option value="{{ $c->id }}" {{ $consultant_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ステータス</label>
                    <select name="status" class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" style="padding: 0.5rem 2rem 0.5rem 0.75rem;">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>全て</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>保留中</option>
                        <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>承認済</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        絞り込み
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">
                埋まっている予約
                <span class="ml-2 text-sm font-normal text-gray-500">（{{ $periods[$period] ?? '' }}）</span>
            </h2>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                {{ $bookings->total() }}件
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">予約ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ユーザー</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">コンサルタント</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">予約日</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">時間</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $booking->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $booking->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $booking->consultant->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $booking->booking_date->format('Y/m/d (D)') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($booking->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">保留中</span>
                                @elseif($booking->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">承認済</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">該当する予約がありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
