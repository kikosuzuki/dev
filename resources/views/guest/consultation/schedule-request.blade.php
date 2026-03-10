@extends('layouts.consultation')

@section('title', '日程調整リクエスト - YCS個別相談')

@section('content')
<div class="mb-4 sm:mb-8">
    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">日程調整のリクエスト</h1>
    <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">ご希望の日時を候補としてお送りください。担当者より折り返しご連絡いたします。</p>
</div>

<div class="bg-white rounded-lg shadow p-4 sm:p-6">
    <form method="POST" action="{{ route('consultation.schedule-request.store') }}">
        @csrf
        @if(request('intro'))
            <input type="hidden" name="intro" value="{{ request('intro') }}">
        @endif

        <div class="space-y-5">
            {{-- お名前 --}}
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

            {{-- メールアドレス --}}
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

            {{-- 電話番号 --}}
            <div>
                <label for="guest_phone" class="block text-sm font-medium text-gray-700 mb-1">
                    電話番号
                </label>
                <input type="tel" id="guest_phone" name="guest_phone" value="{{ old('guest_phone') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                    placeholder="090-1234-5678">
                @error('guest_phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 候補日時 --}}
            <div class="border-t border-gray-200 pt-5">
                <p class="text-sm font-medium text-gray-700 mb-3">ご希望の日時 <span class="text-red-500">*</span></p>
                <p class="text-xs text-gray-500 mb-4">候補1は必須です。複数の候補をご記入いただけるとスムーズです。</p>

                <div class="space-y-3">
                    {{-- 候補1 --}}
                    <div>
                        <label for="candidate_1" class="block text-xs font-medium text-gray-600 mb-1">候補1 <span class="text-red-500">*</span></label>
                        <input type="text" id="candidate_1" name="candidate_1" value="{{ old('candidate_1') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                            placeholder="例: 3月15日（月）10:00〜">
                        @error('candidate_1')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 候補2 --}}
                    <div>
                        <label for="candidate_2" class="block text-xs font-medium text-gray-600 mb-1">候補2</label>
                        <input type="text" id="candidate_2" name="candidate_2" value="{{ old('candidate_2') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                            placeholder="例: 3月16日（火）14:00〜">
                        @error('candidate_2')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 候補3 --}}
                    <div>
                        <label for="candidate_3" class="block text-xs font-medium text-gray-600 mb-1">候補3</label>
                        <input type="text" id="candidate_3" name="candidate_3" value="{{ old('candidate_3') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                            placeholder="例: 3月18日（木）16:00〜">
                        @error('candidate_3')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ご相談内容 --}}
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                    ご相談内容
                </label>
                <textarea id="message" name="message" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                    placeholder="ご相談内容がございましたらご記入ください">{{ old('message') }}</textarea>
                @error('message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- ボタン --}}
        <div class="flex items-center justify-end space-x-4 mt-8">
            <a href="{{ route('consultation.index', request('intro') ? ['intro' => request('intro')] : []) }}"
                class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition text-sm font-medium">
                戻る
            </a>
            <button type="submit"
                class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 transition text-sm font-medium">
                リクエストを送信
            </button>
        </div>
    </form>
</div>
@endsection
