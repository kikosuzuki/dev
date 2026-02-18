@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">予約管理</h1>

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

    {{-- Status Filter Tabs --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
                <a href="{{ route('consultant.bookings.index') }}"
                    class="whitespace-nowrap py-4 px-6 border-b-2 text-sm font-medium
                        {{ !$status ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    すべて
                </a>
                <a href="{{ route('consultant.bookings.index', ['status' => 'pending']) }}"
                    class="whitespace-nowrap py-4 px-6 border-b-2 text-sm font-medium
                        {{ $status === 'pending' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    承認待ち
                </a>
                <a href="{{ route('consultant.bookings.index', ['status' => 'approved']) }}"
                    class="whitespace-nowrap py-4 px-6 border-b-2 text-sm font-medium
                        {{ $status === 'approved' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    承認済み
                </a>
                <a href="{{ route('consultant.bookings.index', ['status' => 'completed']) }}"
                    class="whitespace-nowrap py-4 px-6 border-b-2 text-sm font-medium
                        {{ $status === 'completed' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    完了
                </a>
                <a href="{{ route('consultant.bookings.index', ['status' => 'cancelled']) }}"
                    class="whitespace-nowrap py-4 px-6 border-b-2 text-sm font-medium
                        {{ $status === 'cancelled' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    キャンセル
                </a>
                <a href="{{ route('consultant.bookings.index', ['status' => 'rejected']) }}"
                    class="whitespace-nowrap py-4 px-6 border-b-2 text-sm font-medium
                        {{ $status === 'rejected' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    却下
                </a>
            </nav>
        </div>
    </div>

    {{-- Bookings List --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($bookings->isEmpty())
            <div class="p-8 text-center text-gray-500">
                該当する予約はありません。
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">予約者</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日付</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">時間</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($bookings as $booking)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $booking->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $booking->user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $booking->booking_date->format('Y/m/d') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($booking->status === 'approved') bg-green-100 text-green-800
                                        @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($booking->status === 'completed') bg-blue-100 text-blue-800
                                        @elseif($booking->status === 'cancelled') bg-gray-100 text-gray-800
                                        @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                                        @endif">
                                        @if($booking->status === 'approved') 承認済み
                                        @elseif($booking->status === 'pending') 承認待ち
                                        @elseif($booking->status === 'completed') 完了
                                        @elseif($booking->status === 'cancelled') キャンセル
                                        @elseif($booking->status === 'rejected') 却下
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center space-x-2">
                                        {{-- Approve button (pending only) --}}
                                        @if($booking->isPending())
                                            <form action="{{ route('consultant.bookings.approve', $booking) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                    承認
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Reject button with reason (pending only) --}}
                                        @if($booking->isPending())
                                            <div x-data="{ showRejectModal: false }">
                                                <button type="button" @click="showRejectModal = true"
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    却下
                                                </button>

                                                {{-- Reject Modal --}}
                                                <div x-show="showRejectModal" x-cloak
                                                    class="fixed inset-0 z-50 overflow-y-auto"
                                                    x-transition:enter="ease-out duration-300"
                                                    x-transition:enter-start="opacity-0"
                                                    x-transition:enter-end="opacity-100"
                                                    x-transition:leave="ease-in duration-200"
                                                    x-transition:leave-start="opacity-100"
                                                    x-transition:leave-end="opacity-0">
                                                    <div class="flex items-center justify-center min-h-screen px-4">
                                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showRejectModal = false"></div>
                                                        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
                                                            <h3 class="text-lg font-medium text-gray-900 mb-4">予約を却下</h3>
                                                            <form action="{{ route('consultant.bookings.reject', $booking) }}" method="POST">
                                                                @csrf
                                                                <div class="mb-4">
                                                                    <label for="cancel_reason_{{ $booking->id }}" class="block text-sm font-medium text-gray-700 mb-1">
                                                                        却下理由
                                                                    </label>
                                                                    <textarea id="cancel_reason_{{ $booking->id }}" name="cancel_reason" rows="3"
                                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                                        placeholder="却下理由を入力してください"></textarea>
                                                                </div>
                                                                <div class="flex justify-end space-x-3">
                                                                    <button type="button" @click="showRejectModal = false"
                                                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                                                        キャンセル
                                                                    </button>
                                                                    <button type="submit"
                                                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                                        却下する
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Complete button (approved only) --}}
                                        @if($booking->isApproved())
                                            <form action="{{ route('consultant.bookings.complete', $booking) }}" method="POST"
                                                onsubmit="return confirm('この予約を完了にしますか？');">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    完了にする
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($bookings->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $bookings->appends(['status' => $status])->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
