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
                <a href="{{ route('consultant.bookings.index', ['status' => 'approved']) }}"
                    class="whitespace-nowrap py-4 px-6 border-b-2 text-sm font-medium
                        {{ $status === 'approved' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    確定済み
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">メモ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">相談結果</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($bookings as $booking)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <div class="text-sm font-medium text-gray-900">{{ $booking->bookerName() }}</div>
                                        @if($booking->isGuest())
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">個別相談</span>
                                        @endif
                                    </div>
                                    @if($booking->isGuest())
                                        <div class="text-sm text-gray-500">{{ $booking->guest_email }}</div>
                                        <div class="text-sm text-gray-500">{{ $booking->guest_phone }}</div>
                                    @else
                                        <div class="text-sm text-gray-500">{{ $booking->user->email }}</div>
                                    @endif
                                    @if($booking->notes)
                                        <div class="text-sm text-gray-500 mt-1">
                                            <span class="font-medium text-gray-600">備考:</span> {{ Str::limit($booking->notes, 50) }}
                                        </div>
                                    @endif
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
                                        @elseif($booking->status === 'completed') bg-blue-100 text-blue-800
                                        @elseif($booking->status === 'cancelled') bg-gray-100 text-gray-800
                                        @endif">
                                        @if($booking->status === 'approved') 確定
                                        @elseif($booking->status === 'completed') 完了
                                        @elseif($booking->status === 'cancelled') キャンセル
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4" x-data="{ showNotesModal: false }">
                                    @php
                                        $currentNotes = $booking->admin_notes;
                                    @endphp
                                    @if($currentNotes)
                                        <button type="button" @click="showNotesModal = true"
                                            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            編集
                                        </button>
                                    @else
                                        <button type="button" @click="showNotesModal = true"
                                            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-50 text-gray-400 hover:bg-gray-100 border border-dashed border-gray-300 transition">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            未記入
                                        </button>
                                    @endif

                                    {{-- Notes Edit Modal --}}
                                    <div x-show="showNotesModal" x-cloak @keydown.escape.window="showNotesModal = false"
                                        class="fixed inset-0 z-50 overflow-y-auto" x-transition>
                                        <div class="flex items-center justify-center min-h-screen px-4">
                                            <div class="fixed inset-0 bg-black/50" @click="showNotesModal = false"></div>
                                            <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6 z-10">
                                                <h3 class="text-lg font-semibold text-gray-900 mb-2">管理メモ</h3>
                                                <p class="text-sm text-gray-500 mb-4">{{ $booking->bookerName() }}</p>
                                                <form method="POST" action="{{ route('consultant.bookings.user-notes.update', $booking) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="mb-4">
                                                        <textarea name="admin_notes" rows="6"
                                                            class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                            placeholder="社内用メモを入力...">{{ $currentNotes }}</textarea>
                                                    </div>
                                                    <div class="flex justify-end space-x-3">
                                                        <button type="button" @click="showNotesModal = false"
                                                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">閉じる</button>
                                                        <button type="submit"
                                                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">保存</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center space-x-2">
                                        {{-- Cancel button (approved only) --}}
                                        @if($booking->isApproved())
                                            <div x-data="{ showCancelModal: false }">
                                                <button type="button" @click="showCancelModal = true"
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                                    キャンセル
                                                </button>

                                                {{-- Cancel Modal --}}
                                                <div x-show="showCancelModal" x-cloak
                                                    class="fixed inset-0 z-50 overflow-y-auto"
                                                    x-transition:enter="ease-out duration-300"
                                                    x-transition:enter-start="opacity-0"
                                                    x-transition:enter-end="opacity-100"
                                                    x-transition:leave="ease-in duration-200"
                                                    x-transition:leave-start="opacity-100"
                                                    x-transition:leave-end="opacity-0">
                                                    <div class="flex items-center justify-center min-h-screen px-4">
                                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showCancelModal = false"></div>
                                                        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
                                                            <h3 class="text-lg font-medium text-gray-900 mb-2">予約をキャンセル</h3>
                                                            <p class="text-sm text-gray-500 mb-4">予約者に理由を含めたキャンセル通知が送信されます。</p>
                                                            <form action="{{ route('consultant.bookings.cancel', $booking) }}" method="POST">
                                                                @csrf
                                                                <div class="mb-4">
                                                                    <label for="cancel_reason_cancel_{{ $booking->id }}" class="block text-sm font-medium text-gray-700 mb-1">
                                                                        キャンセル理由 <span class="text-red-500">*</span>
                                                                    </label>
                                                                    <textarea id="cancel_reason_cancel_{{ $booking->id }}" name="cancel_reason" rows="3" required
                                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                                                                        placeholder="キャンセル理由を入力してください（必須）"></textarea>
                                                                </div>
                                                                <div class="flex justify-end space-x-3">
                                                                    <button type="button" @click="showCancelModal = false"
                                                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                                                        閉じる
                                                                    </button>
                                                                    <button type="submit"
                                                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                                                        キャンセルする
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Consultation Record Button (approved or completed) --}}
                                        @if($booking->isApproved() || $booking->status === 'completed')
                                            <div x-data="{ showRecordModal: false }">
                                                <button type="button" @click="showRecordModal = true"
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    記録
                                                </button>

                                                {{-- Record Modal --}}
                                                <div x-show="showRecordModal" x-cloak @keydown.escape.window="showRecordModal = false"
                                                    class="fixed inset-0 z-50 overflow-y-auto" x-transition>
                                                    <div class="flex items-center justify-center min-h-screen px-4">
                                                        <div class="fixed inset-0 bg-black/50" @click="showRecordModal = false"></div>
                                                        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6 z-10">
                                                            <h3 class="text-lg font-semibold text-gray-900 mb-2">相談記録</h3>
                                                            <p class="text-sm text-gray-500 mb-4">{{ $booking->bookerName() }} / {{ $booking->booking_date->format('Y/m/d') }}</p>
                                                            <form method="POST" action="{{ route('consultant.bookings.consultation-record.update', $booking) }}">
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
                                                                    <label class="block text-sm font-medium text-gray-700 mb-2">相談メモ <span class="text-red-500">*</span></label>
                                                                    <textarea name="consultation_notes" rows="5" required
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
