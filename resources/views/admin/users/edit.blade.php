@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">ユーザー編集</h1>
        <p class="mt-1 text-sm text-gray-600">ユーザー情報を編集します: {{ $user->name }}</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6" x-data="{ role: '{{ old('role', $user->role) }}' }">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            {{-- Name --}}
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">名前 <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">メール <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Role --}}
            <div class="mb-6">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">ロール <span class="text-red-500">*</span></label>
                <select name="role" id="role" required x-model="role"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('role') border-red-500 @enderror">
                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>ユーザー</option>
                    <option value="consultant" {{ old('role', $user->role) === 'consultant' ? 'selected' : '' }}>コンサルタント</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>管理者</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone --}}
            <div class="mb-6">
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">電話番号</label>
                <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('phone') border-red-500 @enderror">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Chatwork Settings --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Chatwork連携</h3>
                <div class="space-y-4">
                    <div>
                        <label for="chatwork_id" class="block text-sm font-medium text-gray-700 mb-1">Chatwork ID</label>
                        <input type="text" name="chatwork_id" id="chatwork_id"
                               value="{{ old('chatwork_id', $user->chatwork_id) }}"
                               placeholder="例: 1234567"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('chatwork_id') border-red-500 @enderror">
                        @error('chatwork_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="chatwork_room_id" class="block text-sm font-medium text-gray-700 mb-1">Chatwork Room ID</label>
                        <input type="text" name="chatwork_room_id" id="chatwork_room_id"
                               value="{{ old('chatwork_room_id', $user->chatwork_room_id) }}"
                               placeholder="例: 123456789"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('chatwork_room_id') border-red-500 @enderror">
                        @error('chatwork_room_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Is Active Toggle --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">ステータス</label>
                <div class="flex items-center" x-data="{ isActive: {{ old('is_active', $user->is_active) ? 'true' : 'false' }} }">
                    <button type="button"
                            @click="isActive = !isActive"
                            :class="isActive ? 'bg-blue-600' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            role="switch"
                            :aria-checked="isActive">
                        <span :class="isActive ? 'translate-x-5' : 'translate-x-0'"
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                    <input type="hidden" name="is_active" :value="isActive ? 1 : 0">
                    <span class="ml-3 text-sm" :class="isActive ? 'text-green-600 font-medium' : 'text-gray-500'" x-text="isActive ? '有効' : '無効'"></span>
                </div>
                @error('is_active')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Consultant Profile Fields --}}
            <div x-show="role === 'consultant'" x-transition class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">コンサルタントプロフィール</h3>

                <div class="mb-4">
                    <label for="specialty" class="block text-sm font-medium text-gray-700 mb-1">専門分野</label>
                    <input type="text" name="specialty" id="specialty"
                           value="{{ old('specialty', $user->consultantProfile?->specialty) }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('specialty') border-red-500 @enderror"
                           placeholder="例: 経営戦略、IT、財務">
                    @error('specialty')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Password Change --}}
            <div class="mb-6 p-4 bg-yellow-50 rounded-md border border-yellow-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">パスワード変更</h3>
                <p class="text-xs text-gray-500 mb-4">パスワードを変更する場合のみ入力してください。空欄の場合は変更されません。</p>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">新しいパスワード</label>
                    <input type="password" name="password" id="password"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('password') border-red-500 @enderror"
                           placeholder="新しいパスワードを入力">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">パスワード確認</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="パスワードを再入力">
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    キャンセル
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    更新する
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
