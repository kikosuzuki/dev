@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">新規予約作成</h1>
        <p class="mt-1 text-sm text-gray-600">管理者として予約を作成します。</p>
    </div>

    @if (session('error'))
        <div class="mb-6 bg-red-50 border border-red-300 text-red-700 rounded-md p-4">
            <p class="text-sm">{{ session('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-300 text-red-700 rounded-md p-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6"
         x-data="{
             bookingType: '{{ old('booking_type', 'guest') }}',
             consultantId: '{{ old('consultant_id', '') }}',
             schedules: [],
             scheduleId: '{{ old('schedule_id', '') }}',
             loading: false,
             fetchSchedules() {
                 if (!this.consultantId) {
                     this.schedules = [];
                     this.scheduleId = '';
                     return;
                 }
                 this.loading = true;
                 this.scheduleId = '';
                 fetch(`{{ route('admin.bookings.schedules') }}?consultant_id=${this.consultantId}`, {
                     headers: {
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                     }
                 })
                 .then(r => r.json())
                 .then(data => {
                     this.schedules = data;
                     this.loading = false;
                 })
                 .catch(() => {
                     this.schedules = [];
                     this.loading = false;
                 });
             }
         }"
         x-init="if (consultantId) fetchSchedules()">
        <form method="POST" action="{{ route('admin.bookings.store') }}">
            @csrf

            {{-- 予約種別 --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">予約種別 <span class="text-red-500">*</span></label>
                <div class="flex gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="booking_type" value="guest" x-model="bookingType"
                               class="form-radio text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">個別相談（ゲスト）</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="booking_type" value="member" x-model="bookingType"
                               class="form-radio text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">会員予約</span>
                    </label>
                </div>
            </div>

            {{-- コンサルタント選択 --}}
            <div class="mb-6">
                <label for="consultant_id" class="block text-sm font-medium text-gray-700 mb-1">コンサルタント <span class="text-red-500">*</span></label>
                <select name="consultant_id" id="consultant_id" x-model="consultantId" @change="fetchSchedules()"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">選択してください</option>
                    @foreach ($consultants as $consultant)
                        <option value="{{ $consultant->id }}" {{ old('consultant_id') == $consultant->id ? 'selected' : '' }}>{{ $consultant->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 予約枠選択 --}}
            <div class="mb-6">
                <label for="schedule_id" class="block text-sm font-medium text-gray-700 mb-1">予約枠 <span class="text-red-500">*</span></label>
                <div x-show="loading" class="text-sm text-gray-500 py-2">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-500 inline" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    読み込み中...
                </div>
                <p x-show="!loading && !consultantId" class="text-sm text-gray-400 py-2">コンサルタントを選択してください</p>
                <p x-show="!loading && consultantId && schedules.length === 0" class="text-sm text-amber-600 py-2">利用可能な予約枠がありません</p>
                <select x-show="!loading && schedules.length > 0" name="schedule_id" id="schedule_id" x-model="scheduleId"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">選択してください</option>
                    <template x-for="s in schedules" :key="s.id">
                        <option :value="s.id" x-text="`${s.date_display} ${s.start_time} - ${s.end_time}`"></option>
                    </template>
                </select>
                @error('schedule_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 会員選択 (会員予約時のみ) --}}
            <div class="mb-6" x-show="bookingType === 'member'" x-transition>
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">会員 <span class="text-red-500">*</span></label>
                <select name="user_id" id="user_id"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">選択してください</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                @error('user_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- ゲスト情報 (個別相談時のみ) --}}
            <div x-show="bookingType === 'guest'" x-transition>
                <div class="mb-6">
                    <label for="guest_name" class="block text-sm font-medium text-gray-700 mb-1">お名前 <span class="text-red-500">*</span></label>
                    <input type="text" name="guest_name" id="guest_name" value="{{ old('guest_name') }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('guest_name') border-red-500 @enderror"
                           placeholder="山田 太郎">
                    @error('guest_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="guest_email" class="block text-sm font-medium text-gray-700 mb-1">メールアドレス <span class="text-red-500">*</span></label>
                    <input type="email" name="guest_email" id="guest_email" value="{{ old('guest_email') }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('guest_email') border-red-500 @enderror"
                           placeholder="example@email.com">
                    @error('guest_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="guest_phone" class="block text-sm font-medium text-gray-700 mb-1">電話番号 <span class="text-red-500">*</span></label>
                    <input type="tel" name="guest_phone" id="guest_phone" value="{{ old('guest_phone') }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('guest_phone') border-red-500 @enderror"
                           placeholder="090-1234-5678">
                    @error('guest_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="guest_referrer" class="block text-sm font-medium text-gray-700 mb-1">紹介者</label>
                    <input type="text" name="guest_referrer" id="guest_referrer" value="{{ old('guest_referrer') }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="紹介者名">
                    @error('guest_referrer')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 備考 --}}
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">備考・メッセージ</label>
                <textarea name="notes" id="notes" rows="3"
                          class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                          placeholder="予約に関するメモ">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 管理メモ --}}
            <div class="mb-6">
                <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-1">管理メモ</label>
                <textarea name="admin_notes" id="admin_notes" rows="2"
                          class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                          placeholder="社内用メモ（予約者には表示されません）">{{ old('admin_notes') }}</textarea>
                @error('admin_notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- アクション --}}
            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.bookings.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    キャンセル
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    予約を作成する
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
