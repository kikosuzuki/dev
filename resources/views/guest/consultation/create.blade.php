@extends('layouts.consultation')

@section('title', '個別相談 - 予約フォーム')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('consultation.index', $intro ? ['intro' => $intro] : []) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition mb-4">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            空き日程一覧に戻る
        </a>
        <h1 class="text-2xl font-bold text-gray-900">個別相談の予約</h1>
        <p class="mt-1 text-gray-600">お客様情報をご入力のうえ、予約を確定してください。</p>
        <p class="mt-2 text-sm font-bold text-red-600">※ 経営者・個人事業主の方が対象です。商品が無い方・副業の方は申込をご遠慮下さい。</p>
    </div>

    {{-- コンサルタント情報 --}}
    @if($schedule->consultant)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">担当コンサルタント</h2>
            <div class="flex items-center space-x-4">
                @if($schedule->consultant->consultantProfile?->photo)
                    <img src="{{ Storage::disk('public')->url($schedule->consultant->consultantProfile->photo) }}"
                        alt="{{ $schedule->consultant->name }}"
                        class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                @else
                    <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center border-2 border-gray-200">
                        <span class="text-emerald-600 font-bold text-xl">{{ mb_substr($schedule->consultant->name, 0, 1) }}</span>
                    </div>
                @endif
                <div>
                    <p class="text-base font-semibold text-gray-900">{{ $schedule->consultant->name }}</p>
                    @if($schedule->consultant->consultantProfile?->specialty)
                        <p class="text-sm text-gray-600 mt-0.5">{{ $schedule->consultant->consultantProfile->specialty }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- 予約日時 --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">予約日時</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">日付</dt>
                <dd class="mt-1 text-base font-semibold text-gray-900">
                    {{ $schedule->date->format('Y年m月d日') }}
                    ({{ ['日','月','火','水','木','金','土'][$schedule->date->dayOfWeek] }})
                </dd>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">時間</dt>
                <dd class="mt-1 text-base font-semibold text-gray-900">
                    {{ \Illuminate\Support\Str::substr($schedule->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($schedule->end_time, 0, 5) }}
                </dd>
            </div>
        </dl>
    </div>

    {{-- 予約フォーム --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">お客様情報</h2>
        <form method="POST" action="{{ route('consultation.store') }}">
            @csrf
            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
            @if($intro)
                <input type="hidden" name="guest_referrer" value="{{ $intro }}">
            @endif

            <div class="space-y-5">
                <div>
                    <label for="guest_name" class="block text-sm font-medium text-gray-700 mb-1">
                        お名前 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="guest_name" name="guest_name" value="{{ old('guest_name') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                        placeholder="山田 太郎">
                    @error('guest_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="guest_email" class="block text-sm font-medium text-gray-700 mb-1">
                        メールアドレス <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="guest_email" name="guest_email" value="{{ old('guest_email') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                        placeholder="example@email.com">
                    @error('guest_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="guest_phone" class="block text-sm font-medium text-gray-700 mb-1">
                        電話番号 <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" id="guest_phone" name="guest_phone" value="{{ old('guest_phone') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                        placeholder="090-1234-5678">
                    @error('guest_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                        ご相談内容・メッセージ（任意）
                    </label>
                    <textarea id="notes" name="notes" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                        placeholder="ご相談したい内容があればご記入ください。">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if(!$intro)
                <div>
                    <label for="guest_referrer" class="block text-sm font-medium text-gray-700 mb-1">
                        紹介者（任意）
                    </label>
                    <input type="text" id="guest_referrer" name="guest_referrer" value="{{ old('guest_referrer') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                        placeholder="紹介者名があればご記入ください">
                    @error('guest_referrer')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif
            </div>

            <div class="flex items-center justify-end space-x-4 mt-8">
                <a href="{{ route('consultation.index', $intro ? ['intro' => $intro] : []) }}"
                    class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition text-sm font-medium">
                    キャンセル
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 transition text-sm font-medium">
                    予約を確定する
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
