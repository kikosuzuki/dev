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
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">保留中の予約</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['pending_bookings']) }}</p>
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
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-900">最近の予約</h2>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    全{{ $recentBookings->total() }}件
                </span>
            </div>
            {{-- Consultation Result Filter --}}
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700">相談結果:</label>
                <select name="consultation_result" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" style="padding: 0.375rem 2rem 0.375rem 0.75rem;">
                    <option value="all" {{ ($consultationResult ?? '') == 'all' || !$consultationResult ? 'selected' : '' }}>全て</option>
                    <option value="success" {{ ($consultationResult ?? '') == 'success' ? 'selected' : '' }}>成約</option>
                    <option value="failure" {{ ($consultationResult ?? '') == 'failure' ? 'selected' : '' }}>不成約</option>
                    <option value="pending" {{ ($consultationResult ?? '') == 'pending' ? 'selected' : '' }}>検討中</option>
                    <option value="unrecorded" {{ ($consultationResult ?? '') == 'unrecorded' ? 'selected' : '' }}>未記録（個別相談）</option>
                </select>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
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
                                    @case('pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">保留中</span>
                                        @break
                                    @case('approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">承認済</span>
                                        @break
                                    @case('completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">完了</span>
                                        @break
                                    @case('cancelled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">キャンセル</span>
                                        @break
                                    @case('rejected')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">却下</span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $booking->status }}</span>
                                @endswitch
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($booking->isGuest())
                                    @if($booking->consultation_result === 'success')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">成約</span>
                                    @elseif($booking->consultation_result === 'failure')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">不成約</span>
                                    @elseif($booking->consultation_result === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">検討中</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">未記録</span>
                                    @endif
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-2">
                                    @if($booking->isPending())
                                        <form action="{{ route('admin.bookings.approve', $booking) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                承認
                                            </button>
                                        </form>
                                        <div x-data="{ showRejectModal: false }">
                                            <button type="button" @click="showRejectModal = true"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                却下
                                            </button>
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
                                                        <form action="{{ route('admin.bookings.reject', $booking) }}" method="POST">
                                                            @csrf
                                                            <div class="mb-4">
                                                                <label for="cancel_reason_{{ $booking->id }}" class="block text-sm font-medium text-gray-700 mb-1">却下理由</label>
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

                                    {{-- Consultation Record Button (Guest bookings only) --}}
                                    @if($booking->isGuest())
                                        <div x-data="{ showRecordModal: false }">
                                            <button type="button" @click="showRecordModal = true"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                記録
                                            </button>
                                            <div x-show="showRecordModal" x-cloak @keydown.escape.window="showRecordModal = false"
                                                class="fixed inset-0 z-50 overflow-y-auto" x-transition>
                                                <div class="flex items-center justify-center min-h-screen px-4">
                                                    <div class="fixed inset-0 bg-black/50" @click="showRecordModal = false"></div>
                                                    <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6 z-10">
                                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">相談記録</h3>
                                                        <p class="text-sm text-gray-500 mb-4">{{ $booking->bookerName() }} / {{ $booking->booking_date->format('Y/m/d') }}</p>
                                                        <form method="POST" action="{{ route('admin.bookings.consultation-record.update', $booking) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="mb-4">
                                                                <label class="block text-sm font-medium text-gray-700 mb-2">相談結果 <span class="text-red-500">*</span></label>
                                                                <div class="flex gap-4">
                                                                    <label class="inline-flex items-center">
                                                                        <input type="radio" name="consultation_result" value="success" class="form-radio text-green-600 focus:ring-green-500" {{ $booking->consultation_result === 'success' ? 'checked' : '' }}>
                                                                        <span class="ml-2 text-sm text-gray-700">成約</span>
                                                                    </label>
                                                                    <label class="inline-flex items-center">
                                                                        <input type="radio" name="consultation_result" value="failure" class="form-radio text-red-600 focus:ring-red-500" {{ $booking->consultation_result === 'failure' ? 'checked' : '' }}>
                                                                        <span class="ml-2 text-sm text-gray-700">不成約</span>
                                                                    </label>
                                                                    <label class="inline-flex items-center">
                                                                        <input type="radio" name="consultation_result" value="pending" class="form-radio text-yellow-600 focus:ring-yellow-500" {{ $booking->consultation_result === 'pending' ? 'checked' : '' }}>
                                                                        <span class="ml-2 text-sm text-gray-700">検討中</span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-4">
                                                                <label class="block text-sm font-medium text-gray-700 mb-2">相談メモ</label>
                                                                <textarea name="consultation_notes" rows="5" maxlength="2000"
                                                                    class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-purple-500 focus:border-purple-500"
                                                                    placeholder="相談内容、結果の詳細、フォローアップ事項など...">{{ $booking->consultation_notes }}</textarea>
                                                            </div>
                                                            <div class="flex justify-end space-x-3">
                                                                <button type="button" @click="showRecordModal = false"
                                                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">閉じる</button>
                                                                <button type="submit"
                                                                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700">保存</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($booking->canCancel())
                                        <div x-data="{ cancelOpen: false }">
                                            <button @click="cancelOpen = true" type="button" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-700 transition">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                キャンセル
                                            </button>
                                            <div x-show="cancelOpen" x-cloak @keydown.escape.window="cancelOpen = false" class="fixed inset-0 z-50 overflow-y-auto" x-transition>
                                                <div class="flex items-center justify-center min-h-screen px-4">
                                                    <div class="fixed inset-0 bg-black/50" @click="cancelOpen = false"></div>
                                                    <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6 z-10">
                                                        <div class="flex items-center mb-4">
                                                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-3">
                                                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                                            </div>
                                                            <h3 class="text-lg font-semibold text-gray-900">予約キャンセルの確認</h3>
                                                        </div>
                                                        <div class="mb-4 p-3 bg-gray-50 rounded-md text-sm text-gray-700">
                                                            <p><span class="font-medium">予約者:</span> {{ $booking->bookerName() }}</p>
                                                            <p><span class="font-medium">コンサルタント:</span> {{ $booking->consultant->name ?? '不明' }}</p>
                                                            <p><span class="font-medium">日時:</span> {{ $booking->booking_date->format('Y/m/d') }} {{ $booking->start_time }}</p>
                                                        </div>
                                                        <p class="text-sm text-red-600 mb-4">この操作は取り消せません。予約者に通知が送信されます。</p>
                                                        <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}">
                                                            @csrf
                                                            <div class="mb-4">
                                                                <label class="block text-sm font-medium text-gray-700 mb-2">キャンセル理由 <span class="text-red-500">*</span></label>
                                                                <textarea name="cancel_reason" rows="3" required maxlength="500"
                                                                          class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-red-500 focus:border-red-500"
                                                                          placeholder="キャンセル理由を入力してください..."></textarea>
                                                            </div>
                                                            <div class="flex justify-end space-x-3">
                                                                <button type="button" @click="cancelOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">戻る</button>
                                                                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">キャンセルを実行</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!$booking->isPending() && !$booking->isGuest() && !$booking->canCancel())
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">予約データがありません</td>
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
