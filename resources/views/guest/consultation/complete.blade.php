@extends('layouts.consultation')

@section('title', '個別相談 - 予約完了')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <div class="mx-auto w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        @if($booking->isApproved())
            <h1 class="text-2xl font-bold text-gray-900 mb-2">予約が確定しました</h1>
            <p class="text-gray-600 mb-8">ご入力いただいたメールアドレスに確認メールをお送りしました。</p>
        @else
            <h1 class="text-2xl font-bold text-gray-900 mb-2">予約を受け付けました</h1>
            <p class="text-gray-600 mb-8">担当者が確認後、ご入力いただいたメールアドレスにご連絡いたします。</p>
        @endif

        <div class="bg-gray-50 rounded-lg p-6 text-left mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">予約内容</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">お名前</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $booking->guest_name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">メールアドレス</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $booking->guest_email }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">電話番号</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $booking->guest_phone }}</dd>
                </div>
                <div class="border-t border-gray-200 pt-3 flex justify-between">
                    <dt class="text-sm text-gray-500">日付</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $booking->booking_date->format('Y年m月d日') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">時間</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">ステータス</dt>
                    <dd>
                        @if($booking->isApproved())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                確定
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                確認待ち
                            </span>
                        @endif
                    </dd>
                </div>
                @if($booking->notes)
                    <div class="border-t border-gray-200 pt-3">
                        <dt class="text-sm text-gray-500 mb-1">ご相談内容</dt>
                        <dd class="text-sm text-gray-900">{{ $booking->notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <a href="{{ route('consultation.index') }}"
            class="inline-flex items-center px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 transition text-sm font-medium">
            空き日程一覧に戻る
        </a>
    </div>
</div>
@endsection
