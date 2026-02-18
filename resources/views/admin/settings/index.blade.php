@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">システム設定</h1>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <p class="ml-3 text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Settings Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow" x-data="{ googleCalendarEnabled: {{ ($settings['google_calendar_enabled'] ?? '0') === '1' ? 'true' : 'false' }} }">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">設定項目</h2>
                </div>
                <form method="POST" action="{{ route('admin.settings.update') }}" class="p-6">
                    @csrf
                    @method('PUT')

                    {{-- Booking Settings Section --}}
                    <div class="mb-8">
                        <h3 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">予約設定</h3>

                        <div class="space-y-6">
                            {{-- Cancel Policy Hours --}}
                            <div>
                                <label for="cancel_policy_hours" class="block text-sm font-medium text-gray-700 mb-1">
                                    キャンセルポリシー（時間）
                                </label>
                                <input type="number" name="cancel_policy_hours" id="cancel_policy_hours"
                                       value="{{ old('cancel_policy_hours', $settings['cancel_policy_hours'] ?? 24) }}"
                                       min="0"
                                       class="block w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('cancel_policy_hours') border-red-500 @enderror">
                                <p class="mt-1 text-xs text-gray-500">予約の何時間前までキャンセル可能か設定します。</p>
                                @error('cancel_policy_hours')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Booking Slot Duration --}}
                            <div>
                                <label for="booking_slot_duration" class="block text-sm font-medium text-gray-700 mb-1">
                                    予約スロット時間（分）
                                </label>
                                <input type="number" name="booking_slot_duration" id="booking_slot_duration"
                                       value="{{ old('booking_slot_duration', $settings['booking_slot_duration'] ?? 60) }}"
                                       min="15" step="15"
                                       class="block w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('booking_slot_duration') border-red-500 @enderror">
                                <p class="mt-1 text-xs text-gray-500">1回の予約の時間枠を分単位で設定します。</p>
                                @error('booking_slot_duration')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Max Bookings Per Day --}}
                            <div>
                                <label for="max_bookings_per_day" class="block text-sm font-medium text-gray-700 mb-1">
                                    1日あたりの最大予約数
                                </label>
                                <input type="number" name="max_bookings_per_day" id="max_bookings_per_day"
                                       value="{{ old('max_bookings_per_day', $settings['max_bookings_per_day'] ?? 10) }}"
                                       min="1"
                                       class="block w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('max_bookings_per_day') border-red-500 @enderror">
                                <p class="mt-1 text-xs text-gray-500">コンサルタント1人あたりの1日の最大予約数を設定します。</p>
                                @error('max_bookings_per_day')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- LINE Settings Section --}}
                    <div class="mb-8">
                        <h3 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">LINE連携設定</h3>

                        <div class="space-y-6">
                            {{-- LINE Channel Token --}}
                            <div>
                                <label for="line_channel_token" class="block text-sm font-medium text-gray-700 mb-1">
                                    LINEチャネルトークン
                                </label>
                                <input type="text" name="line_channel_token" id="line_channel_token"
                                       value="{{ old('line_channel_token', $settings['line_channel_token'] ?? '') }}"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('line_channel_token') border-red-500 @enderror"
                                       placeholder="チャネルアクセストークンを入力">
                                @error('line_channel_token')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- LINE Channel Secret --}}
                            <div>
                                <label for="line_channel_secret" class="block text-sm font-medium text-gray-700 mb-1">
                                    LINEチャネルシークレット
                                </label>
                                <input type="password" name="line_channel_secret" id="line_channel_secret"
                                       value="{{ old('line_channel_secret', $settings['line_channel_secret'] ?? '') }}"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('line_channel_secret') border-red-500 @enderror"
                                       placeholder="チャネルシークレットを入力">
                                @error('line_channel_secret')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Google Calendar Settings Section --}}
                    <div class="mb-8">
                        <h3 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Googleカレンダー連携</h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Googleカレンダー連携</label>
                            <div class="flex items-center">
                                <button type="button"
                                        @click="googleCalendarEnabled = !googleCalendarEnabled"
                                        :class="googleCalendarEnabled ? 'bg-blue-600' : 'bg-gray-200'"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        role="switch"
                                        :aria-checked="googleCalendarEnabled">
                                    <span :class="googleCalendarEnabled ? 'translate-x-5' : 'translate-x-0'"
                                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                </button>
                                <input type="hidden" name="google_calendar_enabled" :value="googleCalendarEnabled ? '1' : '0'">
                                <span class="ml-3 text-sm" :class="googleCalendarEnabled ? 'text-green-600 font-medium' : 'text-gray-500'" x-text="googleCalendarEnabled ? '有効' : '無効'"></span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">有効にすると予約がGoogleカレンダーに自動同期されます。</p>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                        <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                            設定を保存
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Audit Log --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">監査ログ</h2>
                    <p class="mt-1 text-xs text-gray-500">最近のシステム操作履歴</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ユーザー</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日時</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($auditLogs as $log)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs font-medium text-gray-900">
                                        {{ $log->user->name ?? '不明' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ $log->action }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                        {{ $log->created_at->format('Y/m/d H:i') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-400 font-mono">
                                        {{ $log->ip_address }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-center text-xs text-gray-500">
                                        ログがありません
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
