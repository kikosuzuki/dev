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

    {{-- Error Message --}}
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="ml-3 text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Settings Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow" x-data="{ googleCalendarEnabled: {{ (optional($settings['google_calendar_enabled'] ?? null)->value ?? '0') === '1' ? 'true' : 'false' }} }">
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
                                       value="{{ old('cancel_policy_hours', optional($settings['cancel_policy_hours'] ?? null)->value ?? 24) }}"
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
                                       value="{{ old('booking_slot_duration', optional($settings['booking_slot_duration'] ?? null)->value ?? 60) }}"
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
                                       value="{{ old('max_bookings_per_day', optional($settings['max_bookings_per_day'] ?? null)->value ?? 10) }}"
                                       min="1"
                                       class="block w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('max_bookings_per_day') border-red-500 @enderror">
                                <p class="mt-1 text-xs text-gray-500">コンサルタント1人あたりの1日の最大予約数を設定します。</p>
                                @error('max_bookings_per_day')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Schedule Per Page --}}
                            <div>
                                <label for="schedule_per_page" class="block text-sm font-medium text-gray-700 mb-1">
                                    予約枠の1ページあたり表示件数
                                </label>
                                <input type="number" name="schedule_per_page" id="schedule_per_page"
                                       value="{{ old('schedule_per_page', optional($settings['schedule_per_page'] ?? null)->value ?? 30) }}"
                                       min="10" max="200" step="10"
                                       class="block w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('schedule_per_page') border-red-500 @enderror">
                                <p class="mt-1 text-xs text-gray-500">予約枠一覧の1ページに表示する最大件数を設定します（10〜200件）。</p>
                                @error('schedule_per_page')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Schedule Disclosure Days --}}
                            <div>
                                <label for="schedule_disclosure_days" class="block text-sm font-medium text-gray-700 mb-1">
                                    予約開示期間（日数）
                                </label>
                                <input type="number" name="schedule_disclosure_days" id="schedule_disclosure_days"
                                       value="{{ old('schedule_disclosure_days', optional($settings['schedule_disclosure_days'] ?? null)->value ?? 30) }}"
                                       min="1"
                                       class="block w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('schedule_disclosure_days') border-red-500 @enderror">
                                <p class="mt-1 text-xs text-gray-500">今日から何日先までの予約枠をユーザーに表示するか設定します。</p>
                                @error('schedule_disclosure_days')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Reminder Settings Section --}}
                    <div class="mb-8">
                        <h3 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">リマインダー設定</h3>

                        <div class="space-y-6">
                            {{-- Reminder Enabled --}}
                            <div x-data="{ reminderEnabled: {{ (optional($settings['reminder_enabled'] ?? null)->value ?? '1') === '1' ? 'true' : 'false' }} }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">リマインダー機能</label>
                                <div class="flex items-center">
                                    <button type="button"
                                            @click="reminderEnabled = !reminderEnabled"
                                            :class="reminderEnabled ? 'bg-blue-600' : 'bg-gray-200'"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                            role="switch">
                                        <span :class="reminderEnabled ? 'translate-x-5' : 'translate-x-0'"
                                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                    </button>
                                    <input type="hidden" name="reminder_enabled" :value="reminderEnabled ? '1' : '0'">
                                    <span class="ml-3 text-sm" :class="reminderEnabled ? 'text-green-600 font-medium' : 'text-gray-500'" x-text="reminderEnabled ? '有効' : '無効'"></span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">無効にするとすべてのリマインド通知が停止されます。</p>
                            </div>

                            {{-- Day-Before Reminder Hour --}}
                            <div>
                                <label for="reminder_day_before_hour" class="block text-sm font-medium text-gray-700 mb-1">
                                    前日リマインド送信時刻
                                </label>
                                <select name="reminder_day_before_hour" id="reminder_day_before_hour"
                                        class="block w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @for($h = 8; $h <= 21; $h++)
                                        <option value="{{ $h }}" {{ (int)(optional($settings['reminder_day_before_hour'] ?? null)->value ?? 18) === $h ? 'selected' : '' }}>{{ sprintf('%02d:00', $h) }}</option>
                                    @endfor
                                </select>
                                <p class="mt-1 text-xs text-gray-500">予約前日のリマインドを送信する時刻です。</p>
                            </div>

                            {{-- Day-Of Reminder Hour --}}
                            <div>
                                <label for="reminder_day_of_hour" class="block text-sm font-medium text-gray-700 mb-1">
                                    当日リマインド送信時刻
                                </label>
                                <select name="reminder_day_of_hour" id="reminder_day_of_hour"
                                        class="block w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @for($h = 6; $h <= 12; $h++)
                                        <option value="{{ $h }}" {{ (int)(optional($settings['reminder_day_of_hour'] ?? null)->value ?? 8) === $h ? 'selected' : '' }}>{{ sprintf('%02d:00', $h) }}</option>
                                    @endfor
                                </select>
                                <p class="mt-1 text-xs text-gray-500">予約当日朝のリマインドを送信する時刻です。</p>
                            </div>

                            {{-- Minutes Before Reminder --}}
                            <div>
                                <label for="reminder_minutes_before" class="block text-sm font-medium text-gray-700 mb-1">
                                    開始前リマインド（分前）
                                </label>
                                <select name="reminder_minutes_before" id="reminder_minutes_before"
                                        class="block w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="5" {{ (int)(optional($settings['reminder_minutes_before'] ?? null)->value ?? 10) === 5 ? 'selected' : '' }}>5分前</option>
                                    <option value="10" {{ (int)(optional($settings['reminder_minutes_before'] ?? null)->value ?? 10) === 10 ? 'selected' : '' }}>10分前</option>
                                    <option value="15" {{ (int)(optional($settings['reminder_minutes_before'] ?? null)->value ?? 10) === 15 ? 'selected' : '' }}>15分前</option>
                                    <option value="30" {{ (int)(optional($settings['reminder_minutes_before'] ?? null)->value ?? 10) === 30 ? 'selected' : '' }}>30分前</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">予約開始の何分前にリマインドを送信するかを設定します。</p>
                            </div>

                            {{-- Default Reminder Message --}}
                            <div>
                                <label for="default_reminder_message" class="block text-sm font-medium text-gray-700 mb-1">
                                    デフォルトリマインドメッセージ
                                </label>
                                <textarea name="default_reminder_message" id="default_reminder_message" rows="3"
                                          class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                          placeholder="お忘れなくご参加ください。">{{ old('default_reminder_message', optional($settings['default_reminder_message'] ?? null)->value ?? '') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">コンサルタントが個別メッセージを設定していない場合に使用されます。空欄時はシステムデフォルトが使用されます。</p>
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
                                       value="{{ old('line_channel_token', optional($settings['line_channel_token'] ?? null)->value ?? '') }}"
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
                                       value="{{ old('line_channel_secret', optional($settings['line_channel_secret'] ?? null)->value ?? '') }}"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('line_channel_secret') border-red-500 @enderror"
                                       placeholder="チャネルシークレットを入力">
                                @error('line_channel_secret')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Chatwork Settings Section --}}
                    <div class="mb-8">
                        <h3 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Chatwork連携設定</h3>

                        <div class="space-y-6">
                            <div>
                                <label for="chatwork_api_token" class="block text-sm font-medium text-gray-700 mb-1">
                                    Chatwork APIトークン
                                </label>
                                <input type="password" name="chatwork_api_token" id="chatwork_api_token"
                                       value="{{ old('chatwork_api_token', optional($settings['chatwork_api_token'] ?? null)->value ?? '') }}"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('chatwork_api_token') border-red-500 @enderror"
                                       placeholder="Chatwork APIトークンを入力">
                                <p class="mt-1 text-xs text-gray-500">Chatwork管理画面から取得したAPIトークンを入力してください。リマインド通知やメッセージ送信に使用されます。</p>
                                @error('chatwork_api_token')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Google Calendar Settings Section --}}
                    <div class="mb-8">
                        <h3 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Googleカレンダー連携</h3>

                        <div class="space-y-6">
                            {{-- Google Account Connection --}}
                            @php
                                $googleRefreshToken = optional($settings['google_refresh_token'] ?? null)->value;
                                $googleAdminEmail = optional($settings['google_admin_email'] ?? null)->value;
                                $isGoogleConnected = !empty($googleRefreshToken);
                            @endphp
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Googleアカウント連携</label>
                                @if($isGoogleConnected)
                                    <div class="flex items-center space-x-3 p-3 bg-green-50 border border-green-200 rounded-md">
                                        <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-green-800">接続済み</p>
                                            @if($googleAdminEmail)
                                                <p class="text-xs text-green-600">{{ $googleAdminEmail }}</p>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('admin.google.disconnect') }}" class="flex-shrink-0"
                                              onsubmit="return confirm('Googleアカウントの連携を解除しますか？')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 border border-red-200 rounded-md hover:bg-red-200 transition">
                                                連携解除
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="flex items-center space-x-3 p-3 bg-gray-50 border border-gray-200 rounded-md">
                                        <svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-600">未接続</p>
                                            <p class="text-xs text-gray-400">Googleアカウントを連携してカレンダー同期を利用できます。</p>
                                        </div>
                                        <a href="{{ route('admin.google.auth') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 transition">
                                            <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" />
                                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                                            </svg>
                                            Googleアカウントを連携
                                        </a>
                                    </div>
                                @endif
                                <p class="mt-1 text-xs text-gray-500">予約確定時にこのアカウントのカレンダーにイベントが作成され、コンサルタントとユーザーに招待が届きます。</p>
                            </div>

                            {{-- Google Calendar Toggle --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">カレンダー自動同期</label>
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
                                <p class="mt-1 text-xs text-gray-500">有効にすると予約がGoogleカレンダーに自動同期されます。Googleアカウントの連携が必要です。</p>
                            </div>
                        </div>
                    </div>

                    {{-- Guest Email Template Settings --}}
                    <div class="mb-8">
                        <h3 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">ゲスト向けメールテンプレート（手動送信用）</h3>
                        <p class="text-xs text-gray-500 mb-4">管理者が<strong>予約一覧画面から手動で</strong>ゲストにメールを送信する際に使用するテンプレートです。予約確定時の自動送信メールには使用されません。本文では <code class="bg-gray-100 px-1 rounded">{name}</code>（ゲスト名）、<code class="bg-gray-100 px-1 rounded">{date}</code>（予約日時）が自動置換されます。</p>

                        <div class="space-y-6">
                            {{-- Confirm Template --}}
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">予約確認テンプレート</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label for="guest_email_tpl_confirm_subject" class="block text-xs font-medium text-gray-600 mb-1">件名</label>
                                        <input type="text" name="guest_email_tpl_confirm_subject" id="guest_email_tpl_confirm_subject"
                                               value="{{ old('guest_email_tpl_confirm_subject', optional($settings['guest_email_tpl_confirm_subject'] ?? null)->value ?? '【予約確認】個別相談のご予約について') }}"
                                               maxlength="200"
                                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="guest_email_tpl_confirm_body" class="block text-xs font-medium text-gray-600 mb-1">本文</label>
                                        <textarea name="guest_email_tpl_confirm_body" id="guest_email_tpl_confirm_body" rows="4"
                                                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('guest_email_tpl_confirm_body', optional($settings['guest_email_tpl_confirm_body'] ?? null)->value ?? "{name}様\n\nご予約の確認をお願いいたします。\n\n■ 日時: {date}\n\nご不明な点がございましたらお気軽にご連絡ください。") }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Remind Template --}}
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">リマインドテンプレート</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label for="guest_email_tpl_remind_subject" class="block text-xs font-medium text-gray-600 mb-1">件名</label>
                                        <input type="text" name="guest_email_tpl_remind_subject" id="guest_email_tpl_remind_subject"
                                               value="{{ old('guest_email_tpl_remind_subject', optional($settings['guest_email_tpl_remind_subject'] ?? null)->value ?? '【リマインド】個別相談のご予約について') }}"
                                               maxlength="200"
                                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="guest_email_tpl_remind_body" class="block text-xs font-medium text-gray-600 mb-1">本文</label>
                                        <textarea name="guest_email_tpl_remind_body" id="guest_email_tpl_remind_body" rows="4"
                                                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('guest_email_tpl_remind_body', optional($settings['guest_email_tpl_remind_body'] ?? null)->value ?? "{name}様\n\n個別相談の予約日時が近づいてまいりました。\n\n■ 日時: {date}\n\nご準備のほどよろしくお願いいたします。") }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Follow-up Template --}}
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">フォローアップテンプレート</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label for="guest_email_tpl_followup_subject" class="block text-xs font-medium text-gray-600 mb-1">件名</label>
                                        <input type="text" name="guest_email_tpl_followup_subject" id="guest_email_tpl_followup_subject"
                                               value="{{ old('guest_email_tpl_followup_subject', optional($settings['guest_email_tpl_followup_subject'] ?? null)->value ?? '【フォローアップ】個別相談について') }}"
                                               maxlength="200"
                                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="guest_email_tpl_followup_body" class="block text-xs font-medium text-gray-600 mb-1">本文</label>
                                        <textarea name="guest_email_tpl_followup_body" id="guest_email_tpl_followup_body" rows="4"
                                                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('guest_email_tpl_followup_body', optional($settings['guest_email_tpl_followup_body'] ?? null)->value ?? "{name}様\n\n先日の個別相談はいかがでしたでしょうか。\nご不明な点やご質問がございましたらお気軽にお問い合わせください。") }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Notice Template --}}
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">お知らせテンプレート</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label for="guest_email_tpl_notice_subject" class="block text-xs font-medium text-gray-600 mb-1">件名</label>
                                        <input type="text" name="guest_email_tpl_notice_subject" id="guest_email_tpl_notice_subject"
                                               value="{{ old('guest_email_tpl_notice_subject', optional($settings['guest_email_tpl_notice_subject'] ?? null)->value ?? '【お知らせ】') }}"
                                               maxlength="200"
                                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="guest_email_tpl_notice_body" class="block text-xs font-medium text-gray-600 mb-1">本文</label>
                                        <textarea name="guest_email_tpl_notice_body" id="guest_email_tpl_notice_body" rows="4"
                                                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('guest_email_tpl_notice_body', optional($settings['guest_email_tpl_notice_body'] ?? null)->value ?? "{name}様\n\nお知らせがございます。\n詳細につきましては下記をご確認ください。\n\n") }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Guest Consultation Settings Section --}}
                    <div class="mb-8">
                        <h3 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">個別相談（ゲスト予約）設定</h3>

                        <div class="space-y-6">
                            {{-- Guest Schedule Disclosure Days --}}
                            <div>
                                <label for="guest_schedule_disclosure_days" class="block text-sm font-medium text-gray-700 mb-1">
                                    ゲスト予約枠の公開期間（日数）
                                </label>
                                @php
                                    $guestDisclosureValue = isset($settings['guest_schedule_disclosure_days']) ? $settings['guest_schedule_disclosure_days']->value : '30';
                                @endphp
                                <div class="flex items-center space-x-2">
                                    <input type="number" name="guest_schedule_disclosure_days" id="guest_schedule_disclosure_days"
                                           value="{{ old('guest_schedule_disclosure_days', $guestDisclosureValue) }}"
                                           min="1" max="365"
                                           class="w-28 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <span class="text-sm text-gray-500">日先まで表示</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">個別相談ページで何日先の予約枠まで表示するかを設定します。会員向けの設定とは独立しています。</p>
                            </div>

                            {{-- Guest Booking Confirmation Message --}}
                            <div>
                                <label for="guest_booking_confirmation_message" class="block text-sm font-medium text-gray-700 mb-1">
                                    個別相談 予約確認メッセージ
                                </label>
                                <textarea name="guest_booking_confirmation_message" id="guest_booking_confirmation_message" rows="3"
                                          class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                          placeholder="予約確認メールに追加するメッセージを入力してください。">{{ old('guest_booking_confirmation_message', optional($settings['guest_booking_confirmation_message'] ?? null)->value ?? '') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">ゲストが予約を完了した際に<strong>自動送信される【予約確定】メール</strong>に追記されるメッセージです。空欄時はデフォルトの文面のみ送信されます。</p>
                            </div>

                            {{-- Guest Reminder Message --}}
                            <div>
                                <label for="guest_reminder_message" class="block text-sm font-medium text-gray-700 mb-1">
                                    個別相談 リマインドメッセージ
                                </label>
                                <textarea name="guest_reminder_message" id="guest_reminder_message" rows="3"
                                          class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                          placeholder="お忘れなくご参加ください。">{{ old('guest_reminder_message', optional($settings['guest_reminder_message'] ?? null)->value ?? '') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">個別相談客へのリマインドメールに表示されるメッセージです。空欄時は「お忘れなくご参加ください。」が使用されます。</p>
                            </div>
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
