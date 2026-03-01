@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('user.bookings.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition mb-4">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            予約一覧に戻る
        </a>
        <h1 class="text-2xl font-bold text-gray-900">予約詳細</h1>
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

    {{-- 予約情報カード --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        {{-- ステータスバッジ --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900">予約情報</h2>
            @switch($booking->status)
                @case('pending')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">保留中</span>
                    @break
                @case('approved')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">承認済み</span>
                    @break
                @case('completed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">完了</span>
                    @break
                @case('cancelled')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">キャンセル済み</span>
                    @break
                @case('rejected')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">拒否</span>
                    @break
            @endswitch
        </div>

        {{-- コンサルタント情報 --}}
        <div class="flex items-center space-x-4 mb-6 pb-6 border-b">
            @if ($booking->consultant->consultantProfile && $booking->consultant->consultantProfile->getPhotoUrl())
                <img src="{{ $booking->consultant->consultantProfile->getPhotoUrl() }}" alt="{{ $booking->consultant->name }}" class="w-16 h-16 rounded-full object-cover">
            @else
                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-indigo-600 font-semibold text-xl">{{ mb_substr($booking->consultant->name, 0, 1) }}</span>
                </div>
            @endif
            <div>
                <h3 class="text-base font-semibold text-gray-900">{{ $booking->consultant->name }}</h3>
                @if ($booking->consultant->consultantProfile)
                    <p class="text-sm text-gray-500">{{ $booking->consultant->consultantProfile->specialty }}</p>
                @endif
            </div>
        </div>

        {{-- 予約詳細 --}}
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">予約日</dt>
                <dd class="mt-1 text-base font-semibold text-gray-900">{{ $booking->booking_date->format('Y年m月d日') }}</dd>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">時間</dt>
                <dd class="mt-1 text-base font-semibold text-gray-900">{{ \Illuminate\Support\Str::substr($booking->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($booking->end_time, 0, 5) }}</dd>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">予約ID</dt>
                <dd class="mt-1 text-base font-semibold text-gray-900">#{{ $booking->id }}</dd>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">予約登録日時</dt>
                <dd class="mt-1 text-base font-semibold text-gray-900">{{ $booking->created_at->format('Y年m月d日 H:i') }}</dd>
            </div>
        </dl>

        {{-- 備考 --}}
        @if ($booking->notes)
            <div class="mt-4 bg-gray-50 rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">備考</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $booking->notes }}</dd>
            </div>
        @endif

        {{-- キャンセル理由 --}}
        @if ($booking->status === 'cancelled' && $booking->cancel_reason)
            <div class="mt-4 bg-red-50 rounded-lg p-4">
                <dt class="text-sm font-medium text-red-500">キャンセル理由</dt>
                <dd class="mt-1 text-sm text-red-700">{{ $booking->cancel_reason }}</dd>
            </div>
        @endif

        {{-- ミーティングURL --}}
        @if ($booking->meeting_url && in_array($booking->status, ['approved', 'completed']))
            <div class="mt-4 bg-indigo-50 rounded-lg p-4">
                <dt class="text-sm font-medium text-indigo-500">ミーティングURL</dt>
                <dd class="mt-1">
                    <a href="{{ $booking->meeting_url }}" target="_blank" rel="noopener noreferrer"
                        class="text-sm text-indigo-600 hover:text-indigo-500 underline break-all">
                        {{ $booking->meeting_url }}
                    </a>
                </dd>
            </div>
        @endif
    </div>

    {{-- キャンセルセクション --}}
    @if ($booking->canCancel())
        <div class="bg-white rounded-lg shadow-md p-6 mb-6" x-data="{ showCancelForm: false }">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">予約キャンセル</h2>
                <button @click="showCancelForm = !showCancelForm"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition text-sm font-medium"
                    x-text="showCancelForm ? '閉じる' : 'キャンセルする'">
                </button>
            </div>

            <div x-show="showCancelForm" x-cloak class="mt-4 pt-4 border-t">
                <form method="POST" action="{{ route('user.bookings.cancel', $booking) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="cancel_reason" class="block text-sm font-medium text-gray-700 mb-1">キャンセル理由（任意）</label>
                        <textarea id="cancel_reason" name="cancel_reason" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 text-sm"
                            placeholder="キャンセルの理由を入力してください。">{{ old('cancel_reason') }}</textarea>
                        @error('cancel_reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-end">
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition text-sm font-medium"
                            onclick="return confirm('本当にキャンセルしますか？')">
                            キャンセルを確定する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- レビューセクション --}}
    @if ($booking->status === 'completed')
        <div class="bg-white rounded-lg shadow-md p-6">
            @if ($booking->review)
                {{-- 投稿済みレビュー表示 --}}
                <h2 class="text-lg font-semibold text-gray-900 mb-4">あなたのレビュー</h2>
                <div class="flex items-center space-x-1 mb-2">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $i <= $booking->review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                    <span class="ml-2 text-sm text-gray-600">{{ $booking->review->rating }}/5</span>
                </div>
                @if ($booking->review->comment)
                    <p class="text-sm text-gray-700">{{ $booking->review->comment }}</p>
                @endif
            @else
                {{-- レビュー投稿フォーム --}}
                <h2 class="text-lg font-semibold text-gray-900 mb-4">レビューを投稿する</h2>
                <p class="text-sm text-gray-600 mb-4">このコンサルティングのご感想をお聞かせください。</p>

                <form method="POST" action="{{ route('user.reviews.store', $booking) }}" x-data="{ rating: 0, hoverRating: 0 }">
                    @csrf

                    {{-- 星評価 --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">評価</label>
                        <div class="flex items-center space-x-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" @click="rating = {{ $i }}" @mouseenter="hoverRating = {{ $i }}" @mouseleave="hoverRating = 0"
                                    class="focus:outline-none transition">
                                    <svg class="w-8 h-8" :class="(hoverRating || rating) >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                            @endfor
                            <span class="ml-2 text-sm text-gray-500" x-show="rating > 0" x-text="rating + '/5'"></span>
                        </div>
                        <input type="hidden" name="rating" :value="rating">
                        @error('rating')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- コメント --}}
                    <div class="mb-4">
                        <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">コメント（任意）</label>
                        <textarea id="comment" name="comment" rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            placeholder="コンサルティングのご感想をお書きください。">{{ old('comment') }}</textarea>
                        @error('comment')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-sm font-medium"
                            :disabled="rating === 0"
                            :class="rating === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                            レビューを投稿する
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @endif
</div>
@endsection
