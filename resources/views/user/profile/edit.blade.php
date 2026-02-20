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
                <div class="p-4 bg-gray-50 rounded-md border border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Chatwork連携</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="chatwork_id" class="block text-sm font-medium text-gray-700 mb-1">Chatwork ID</label>
                            <input
                                type="text"
                                id="chatwork_id"
                                name="chatwork_id"
                                value="{{ old('chatwork_id', $user->chatwork_id) }}"
                                placeholder="例: 1234567"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('chatwork_id') border-red-300 @enderror"
                            >
                            <p class="mt-1 text-xs text-gray-500">ChatworkのアカウントIDを入力してください。</p>
                            @error('chatwork_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="chatwork_room_id" class="block text-sm font-medium text-gray-700 mb-1">Chatwork Room ID</label>
                            <input
                                type="text"
                                id="chatwork_room_id"
                                name="chatwork_room_id"
                                value="{{ old('chatwork_room_id', $user->chatwork_room_id) }}"
                                placeholder="例: 123456789"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('chatwork_room_id') border-red-300 @enderror"
                            >
                            <p class="mt-1 text-xs text-gray-500">通知を受け取るルームのIDを入力してください。</p>
                            @error('chatwork_room_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Notification Channel --}}
                <div>
                    <label for="notification_channel" class="block text-sm font-medium text-gray-700 mb-1">通知方法</label>
                    <select
                        id="notification_channel"
                        name="notification_channel"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notification_channel') border-red-300 @enderror"
                    >
                        <option value="email" {{ old('notification_channel', $user->notification_channel) === 'email' ? 'selected' : '' }}>メール</option>
                        <option value="line" {{ old('notification_channel', $user->notification_channel) === 'line' ? 'selected' : '' }}>LINE</option>
                        <option value="both" {{ old('notification_channel', $user->notification_channel) === 'both' ? 'selected' : '' }}>両方</option>
                    </select>
                    @error('notification_channel')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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
