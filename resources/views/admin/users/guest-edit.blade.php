@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">ゲスト相談者 編集</h1>
        <p class="mt-1 text-sm text-gray-600">ゲスト相談者の情報を編集します: {{ $booking->guest_name }}</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        {{-- Booking Info (Read-only) --}}
        <div class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">予約情報</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">予約ID</dt>
                    <dd class="font-medium text-gray-900">#{{ $booking->id }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">相談日時</dt>
                    <dd class="font-medium text-gray-900">{{ $booking->booking_date->format('Y/m/d') }} {{ substr($booking->start_time, 0, 5) }}-{{ substr($booking->end_time, 0, 5) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">担当コンサルタント</dt>
                    <dd class="font-medium text-gray-900">{{ $booking->consultant->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">ステータス</dt>
                    <dd>
                        @switch($booking->status)
                            @case('approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">承認済</span>
                                @break
                            @case('pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">確認待ち</span>
                                @break
                            @case('completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">完了</span>
                                @break
                            @case('cancelled')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">キャンセル</span>
                                @break
                            @case('rejected')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">却下</span>
                                @break
                        @endswitch
                    </dd>
                </div>
            </dl>
        </div>

        <form method="POST" action="{{ route('admin.users.guest.update', $booking) }}">
            @csrf
            @method('PUT')

            {{-- Guest Name --}}
            <div class="mb-6">
                <label for="guest_name" class="block text-sm font-medium text-gray-700 mb-1">名前 <span class="text-red-500">*</span></label>
                <input type="text" name="guest_name" id="guest_name" value="{{ old('guest_name', $booking->guest_name) }}" required
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('guest_name') border-red-500 @enderror">
                @error('guest_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Guest Email --}}
            <div class="mb-6">
                <label for="guest_email" class="block text-sm font-medium text-gray-700 mb-1">メール <span class="text-red-500">*</span></label>
                <input type="email" name="guest_email" id="guest_email" value="{{ old('guest_email', $booking->guest_email) }}" required
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('guest_email') border-red-500 @enderror">
                @error('guest_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Guest Phone --}}
            <div class="mb-6">
                <label for="guest_phone" class="block text-sm font-medium text-gray-700 mb-1">電話番号 <span class="text-red-500">*</span></label>
                <input type="tel" name="guest_phone" id="guest_phone" value="{{ old('guest_phone', $booking->guest_phone) }}" required
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('guest_phone') border-red-500 @enderror">
                @error('guest_phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Guest Referrer --}}
            <div class="mb-6">
                <label for="guest_referrer" class="block text-sm font-medium text-gray-700 mb-1">紹介者</label>
                <input type="text" name="guest_referrer" id="guest_referrer" value="{{ old('guest_referrer', $booking->guest_referrer) }}"
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('guest_referrer') border-red-500 @enderror"
                       placeholder="紹介者名を入力...">
                @error('guest_referrer')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">紹介者の付け替えが必要な場合は、こちらを変更してください。</p>
            </div>

            {{-- Admin Notes --}}
            <div class="mb-6">
                <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-1">管理メモ</label>
                <textarea name="admin_notes" id="admin_notes" rows="4"
                          class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('admin_notes') border-red-500 @enderror"
                          placeholder="社内用のメモを入力（コンサルタントからも閲覧・編集可能）">{{ old('admin_notes', $booking->admin_notes) }}</textarea>
                @error('admin_notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">コンサルタントの予約一覧からも閲覧・編集可能です。</p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.users.index', ['role' => 'guest']) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    キャンセル
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    更新する
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
