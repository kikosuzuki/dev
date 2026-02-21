@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">ダッシュボード</h1>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">総予約数</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_bookings'] ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">完了済み</div>
            <div class="mt-2 text-3xl font-bold text-green-600">{{ $stats['completed'] ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">今月の予約</div>
            <div class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['monthly_bookings'] ?? 0 }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Today's Bookings --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">本日の予約</h2>
            </div>
            <div class="p-6">
                @if($todayBookings->isEmpty())
                    <p class="text-gray-500 text-sm">本日の予約はありません。</p>
                @else
                    <div class="space-y-4">
                        @foreach($todayBookings as $booking)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $booking->bookerName() }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($booking->status === 'approved') bg-green-100 text-green-800
                                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($booking->status === 'completed') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @if($booking->status === 'approved') 承認済み
                                    @elseif($booking->status === 'pending') 承認待ち
                                    @elseif($booking->status === 'completed') 完了
                                    @elseif($booking->status === 'cancelled') キャンセル
                                    @elseif($booking->status === 'rejected') 却下
                                    @else {{ $booking->status }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Pending Approval Requests --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">承認待ちリクエスト</h2>
            </div>
            <div class="p-6">
                @if($pendingBookings->isEmpty())
                    <p class="text-gray-500 text-sm">承認待ちの予約はありません。</p>
                @else
                    <div class="space-y-4">
                        @foreach($pendingBookings as $booking)
                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $booking->bookerName() }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $booking->booking_date->format('Y/m/d') }}
                                            {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                                @if($booking->notes)
                                    <p class="text-sm text-gray-600 mb-3">{{ $booking->notes }}</p>
                                @endif
                                <div class="flex space-x-2">
                                    <form action="{{ route('consultant.bookings.approve', $booking) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            承認
                                        </button>
                                    </form>
                                    <form action="{{ route('consultant.bookings.reject', $booking) }}" method="POST"
                                        onsubmit="return confirm('この予約を却下しますか？');">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            却下
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Upcoming Bookings --}}
    <div class="mt-8 bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">今後の予約</h2>
        </div>
        <div class="p-6">
            @if($upcomingBookings->isEmpty())
                <p class="text-gray-500 text-sm">今後の予約はありません。</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日付</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">時間</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">予約者</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($upcomingBookings as $booking)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $booking->booking_date->format('Y/m/d') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $booking->bookerName() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($booking->status === 'approved') bg-green-100 text-green-800
                                            @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            @if($booking->status === 'approved') 承認済み
                                            @elseif($booking->status === 'pending') 承認待ち
                                            @else {{ $booking->status }}
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
