@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">プロフィール設定</h1>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-300 text-green-700 rounded-md p-4">
            {{ session('success') }}
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

    <form action="{{ route('consultant.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow divide-y divide-gray-200">
            {{-- Basic Information --}}
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">基本情報</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">氏名</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">電話番号</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            {{-- Profile Photo --}}
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">プロフィール写真</h2>
                <div class="flex items-center space-x-6">
                    @if($profile && $profile->photo)
                        <div class="shrink-0">
                            <img class="h-20 w-20 object-cover rounded-full" src="{{ asset('storage/' . $profile->photo) }}" alt="プロフィール写真">
                        </div>
                    @else
                        <div class="shrink-0 h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center">
                            <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    @endif
                    <div>
                        <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">写真を変更</label>
                        <input type="file" id="photo" name="photo" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG形式。最大2MB。</p>
                    </div>
                </div>
            </div>

            {{-- Professional Information --}}
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">専門情報</h2>
                <div class="space-y-6">
                    <div>
                        <label for="specialty" class="block text-sm font-medium text-gray-700 mb-1">専門分野</label>
                        <input type="text" id="specialty" name="specialty" value="{{ old('specialty', $profile->specialty ?? '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="例: キャリアコンサルティング">
                    </div>
                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">自己紹介</label>
                        <textarea id="bio" name="bio" rows="5"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="経歴や得意分野について記載してください">{{ old('bio', $profile->bio ?? '') }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="experience_years" class="block text-sm font-medium text-gray-700 mb-1">経験年数</label>
                            <input type="number" id="experience_years" name="experience_years" value="{{ old('experience_years', $profile->experience_years ?? '') }}" min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="10">
                        </div>
                    </div>
                    <div>
                        <label for="qualifications" class="block text-sm font-medium text-gray-700 mb-1">資格</label>
                        <input type="text" id="qualifications" name="qualifications"
                            value="{{ old('qualifications', $profile && $profile->qualifications ? implode(', ', $profile->qualifications) : '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="資格をカンマ区切りで入力（例: MBA, 中小企業診断士, PMP）">
                        <p class="mt-1 text-xs text-gray-500">複数の場合はカンマ（,）で区切ってください。</p>
                    </div>
                    <div>
                        <label for="languages" class="block text-sm font-medium text-gray-700 mb-1">対応言語</label>
                        <input type="text" id="languages" name="languages"
                            value="{{ old('languages', $profile && $profile->languages ? implode(', ', $profile->languages) : '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="対応言語をカンマ区切りで入力（例: 日本語, 英語, 中国語）">
                        <p class="mt-1 text-xs text-gray-500">複数の場合はカンマ（,）で区切ってください。</p>
                    </div>
                </div>
            </div>

            {{-- Meeting Settings --}}
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">ミーティング設定</h2>
                <div class="space-y-6">
                    <div>
                        <label for="meeting_url" class="block text-sm font-medium text-gray-700 mb-1">ミーティングURL（Zoom等）</label>
                        <input type="url" id="meeting_url" name="meeting_url" value="{{ old('meeting_url', $profile->meeting_url ?? '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="https://zoom.us/j/1234567890">
                        <p class="mt-1 text-xs text-gray-500">リマインド通知に含まれるミーティングリンクです。Zoom、Google Meet等のURLを入力してください。</p>
                    </div>
                    <div>
                        <label for="important_document_url" class="block text-sm font-medium text-gray-700 mb-1">重要事項説明書URL</label>
                        <input type="url" id="important_document_url" name="important_document_url" value="{{ old('important_document_url', $profile->important_document_url ?? '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="https://example.com/document.pdf">
                        <p class="mt-1 text-xs text-gray-500">重要事項説明書のダウンロードURLを入力してください。メール送信時に {important_document_url} タグで利用できます。</p>
                    </div>
                    <div>
                        <label for="chatwork_account_id" class="block text-sm font-medium text-gray-700 mb-1">Chatwork アカウントID</label>
                        <input type="text" id="chatwork_account_id" name="chatwork_account_id" value="{{ old('chatwork_account_id', $profile->chatwork_account_id ?? '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="1234567">
                        <p class="mt-1 text-xs text-gray-500">Chatwork通知でTO指定に使用されるアカウントIDです。Chatworkのプロフィールから確認できます。</p>
                    </div>
                </div>
            </div>

            {{-- Settings --}}
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">設定</h2>
                <div class="space-y-6">
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-1">メール通知</span>
                        <p class="text-xs text-gray-500 mb-2">予約の確定・キャンセル・リマインド等の通知を受け取りたい場合はチェックを入れてください。</p>
                        <label class="inline-flex items-center">
                            <input type="hidden" name="notify_email" value="0">
                            <input type="checkbox" name="notify_email" value="1"
                                {{ old('notify_email', $user->notify_email) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">メールで通知を受け取る</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    保存する
                </button>
            </div>
        </div>
    </form>

    {{-- Google Calendar Integration (separate from main profile form) --}}
    <div class="bg-white rounded-lg shadow mt-8">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Googleカレンダー連携</h2>

            @if($profile && $profile->isGoogleConnected())
                {{-- Connected State --}}
                <div class="flex items-center space-x-3 p-3 bg-green-50 border border-green-200 rounded-md">
                    <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-green-800">接続済み</p>
                        @if($profile->google_calendar_email)
                            <p class="text-xs text-green-600">{{ $profile->google_calendar_email }}</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('consultant.google.disconnect') }}"
                          onsubmit="return confirm('Googleアカウントの連携を解除しますか？')">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 border border-red-200 rounded-md hover:bg-red-200 transition">
                            連携解除
                        </button>
                    </form>
                </div>

                {{-- Calendar Selection --}}
                <div class="mt-4" x-data="calendarSelector()">
                    <label class="block text-sm font-medium text-gray-700 mb-2">同期先カレンダー</label>
                    <div class="flex items-center space-x-3">
                        <template x-if="loading">
                            <select disabled class="block w-full max-w-md border-gray-300 rounded-md shadow-sm sm:text-sm bg-gray-100">
                                <option>読み込み中...</option>
                            </select>
                        </template>
                        <template x-if="!loading">
                            <select x-model="selectedCalendar"
                                    class="block w-full max-w-md border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <template x-for="cal in calendars" :key="cal.id">
                                    <option :value="cal.id" x-text="cal.summary + (cal.primary ? ' (メイン)' : '')" :selected="cal.id === selectedCalendar"></option>
                                </template>
                            </select>
                        </template>
                        <button @click="saveCalendar()"
                                :disabled="saving || loading"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition whitespace-nowrap">
                            <span x-show="!saving">保存</span>
                            <span x-show="saving">保存中...</span>
                        </button>
                    </div>
                    <p x-show="error" x-text="error" class="mt-1 text-sm text-red-600"></p>
                    <p x-show="successMsg" x-text="successMsg" class="mt-1 text-sm text-green-600"></p>
                    <p class="mt-1 text-xs text-gray-500">予約が確定した際にイベントが作成されるカレンダーを選択してください。</p>
                </div>

                <script>
                    function calendarSelector() {
                        return {
                            calendars: [],
                            selectedCalendar: '{{ $profile->google_calendar_id ?? "primary" }}',
                            loading: true,
                            saving: false,
                            error: '',
                            successMsg: '',
                            init() {
                                fetch('{{ route("consultant.google.calendars") }}', {
                                    headers: { 'Accept': 'application/json' }
                                })
                                .then(r => r.json())
                                .then(data => {
                                    this.calendars = data.calendars || [];
                                    if (data.selected && this.calendars.length > 0) {
                                        this.selectedCalendar = data.selected;
                                    }
                                    this.loading = false;
                                })
                                .catch(() => {
                                    this.error = 'カレンダー一覧の取得に失敗しました。';
                                    this.loading = false;
                                });
                            },
                            saveCalendar() {
                                this.saving = true;
                                this.error = '';
                                this.successMsg = '';
                                fetch('{{ route("consultant.google.calendar.update") }}', {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({ google_calendar_id: this.selectedCalendar })
                                })
                                .then(r => {
                                    if (r.ok) {
                                        this.successMsg = 'カレンダーを変更しました。';
                                    } else {
                                        this.error = 'カレンダーの保存に失敗しました。';
                                    }
                                    this.saving = false;
                                })
                                .catch(() => {
                                    this.error = 'カレンダーの保存に失敗しました。';
                                    this.saving = false;
                                });
                            }
                        };
                    }
                </script>
            @else
                {{-- Disconnected State --}}
                <div class="flex items-center space-x-3 p-3 bg-gray-50 border border-gray-200 rounded-md">
                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm text-gray-600">未接続</p>
                        <p class="text-xs text-gray-400">Googleアカウントを連携すると、予約がご自身のカレンダーに自動同期されます。</p>
                    </div>
                    <a href="{{ route('consultant.google.auth') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 transition">
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
            <p class="mt-2 text-xs text-gray-500">連携すると、予約が確定した際にご自身のGoogleカレンダーにイベントが自動作成されます。</p>
        </div>
    </div>
</div>
@endsection
