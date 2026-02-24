@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">マイページ</h1>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-green-800 text-sm font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- Profile Edit Form --}}
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">プロフィール編集</h2>

        <form method="POST" action="{{ route('user.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                {{-- Avatar Upload --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">プロフィール画像</label>
                    <div class="flex items-center gap-4">
                        @if($user->avatar)
                            <img
                                src="{{ Storage::url($user->avatar) }}"
                                alt="{{ $user->name }}"
                                class="w-16 h-16 rounded-full object-cover"
                            >
                        @else
                            <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center">
                                <span class="text-indigo-600 font-bold text-xl">{{ mb_substr($user->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div>
                            <input
                                type="file"
                                id="avatar"
                                name="avatar"
                                accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            >
                            <p class="mt-1 text-xs text-gray-500">JPG、PNG形式。最大2MBまで。</p>
                        </div>
                    </div>
                    @error('avatar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">名前</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-300 @enderror"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">メールアドレス</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-300 @enderror"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">電話番号</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="{{ old('phone', $user->phone) }}"
                        placeholder="090-1234-5678"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('phone') border-red-300 @enderror"
                    >
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Chatwork Settings --}}
                <div class="p-4 bg-gray-50 rounded-md border border-gray-200" x-data="chatworkMembers()">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Chatwork連携</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="chatwork_room_id" class="block text-sm font-medium text-gray-700 mb-1">Chatwork Room ID</label>
                            <div class="flex space-x-2">
                                <input
                                    type="text"
                                    id="chatwork_room_id"
                                    name="chatwork_room_id"
                                    x-model="roomId"
                                    value="{{ old('chatwork_room_id', $user->chatwork_room_id) }}"
                                    placeholder="例: 123456789"
                                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('chatwork_room_id') border-red-300 @enderror"
                                >
                                <button type="button" @click="fetchMembers()"
                                    :disabled="loading || !roomId"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-show="!loading">メンバー取得</span>
                                    <span x-show="loading" x-cloak>取得中...</span>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">通知を受け取るルームのIDを入力し「メンバー取得」を押してください。</p>
                            @error('chatwork_room_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="chatwork_id" class="block text-sm font-medium text-gray-700 mb-1">通知先メンバー（To指定）</label>
                            <template x-if="members.length > 0">
                                <select name="chatwork_id" id="chatwork_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">選択してください</option>
                                    <template x-for="member in members" :key="member.account_id">
                                        <option :value="member.account_id" :selected="member.account_id == selectedId" x-text="member.name + ' (' + member.account_id + ')'"></option>
                                    </template>
                                </select>
                            </template>
                            <template x-if="members.length === 0">
                                <input
                                    type="text"
                                    id="chatwork_id"
                                    name="chatwork_id"
                                    value="{{ old('chatwork_id', $user->chatwork_id) }}"
                                    placeholder="例: 1234567"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('chatwork_id') border-red-300 @enderror"
                                >
                            </template>
                            <p class="mt-1 text-xs text-gray-500" x-show="members.length === 0">Room IDを入力して「メンバー取得」を押すとメンバー一覧から選択できます。</p>
                            <p class="mt-1 text-sm text-red-600" x-show="errorMessage" x-text="errorMessage" x-cloak></p>
                            @error('chatwork_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <script>
                        function chatworkMembers() {
                            return {
                                roomId: '{{ old('chatwork_room_id', $user->chatwork_room_id) }}',
                                selectedId: '{{ old('chatwork_id', $user->chatwork_id) }}',
                                members: [],
                                loading: false,
                                errorMessage: '',
                                async fetchMembers() {
                                    if (!this.roomId) return;
                                    this.loading = true;
                                    this.errorMessage = '';
                                    this.members = [];
                                    try {
                                        const res = await fetch(`/api/chatwork/members/${this.roomId}`);
                                        if (res.ok) {
                                            this.members = await res.json();
                                        } else {
                                            const data = await res.json();
                                            this.errorMessage = data.error || 'メンバーの取得に失敗しました。';
                                        }
                                    } catch (e) {
                                        this.errorMessage = '通信エラーが発生しました。';
                                    } finally {
                                        this.loading = false;
                                    }
                                }
                            }
                        }
                    </script>
                </div>

                {{-- Notification Channels --}}
                <div>
                    <span class="block text-sm font-medium text-gray-700 mb-2">通知方法</span>
                    <div class="space-y-2">
                        <label class="inline-flex items-center">
                            <input type="hidden" name="notify_email" value="0">
                            <input type="checkbox" name="notify_email" value="1"
                                {{ old('notify_email', $user->notify_email) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">メール</span>
                        </label>
                        <br>
                        <label class="inline-flex items-center">
                            <input type="hidden" name="notify_line" value="0">
                            <input type="checkbox" name="notify_line" value="1"
                                {{ old('notify_line', $user->notify_line) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">LINE</span>
                        </label>
                        <br>
                        <label class="inline-flex items-center">
                            <input type="hidden" name="notify_chatwork" value="0">
                            <input type="checkbox" name="notify_chatwork" value="1"
                                {{ old('notify_chatwork', $user->notify_chatwork) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Chatwork</span>
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Chatwork通知にはRoom IDの設定が必要です。</p>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                    プロフィールを更新
                </button>
            </div>
        </form>
    </div>

    {{-- Password Change Form --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">パスワード変更</h2>

        <form method="POST" action="{{ route('user.profile.password') }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                {{-- Current Password --}}
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">現在のパスワード</label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('current_password') border-red-300 @enderror"
                    >
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">新しいパスワード</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('password') border-red-300 @enderror"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Confirmation --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">新しいパスワード（確認）</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center px-6 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 transition">
                    パスワードを変更
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
