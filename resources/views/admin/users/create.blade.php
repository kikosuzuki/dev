@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">ユーザー登録</h1>
        <p class="mt-1 text-sm text-gray-600">新しいユーザーを登録します。</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6" x-data="{ role: '{{ old('role', 'user') }}' }">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            {{-- Name --}}
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">名前 <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-500 @enderror"
                       placeholder="山田 太郎">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">メール <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('email') border-red-500 @enderror"
                       placeholder="example@email.com">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">パスワード <span class="text-red-500">*</span></label>
                <input type="password" name="password" id="password" required
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('password') border-red-500 @enderror"
                       placeholder="8文字以上のパスワード">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Confirmation --}}
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">パスワード確認 <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                       placeholder="パスワードを再入力">
            </div>

            {{-- Role --}}
            <div class="mb-6">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">ロール <span class="text-red-500">*</span></label>
                <select name="role" id="role" required x-model="role"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('role') border-red-500 @enderror">
                    <option value="user">ユーザー</option>
                    <option value="consultant">コンサルタント</option>
                    <option value="admin">管理者</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone --}}
            <div class="mb-6">
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">電話番号</label>
                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('phone') border-red-500 @enderror"
                       placeholder="090-1234-5678">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Specialty (shown only when role is consultant) --}}
            <div class="mb-6" x-show="role === 'consultant'" x-transition>
                <label for="specialty" class="block text-sm font-medium text-gray-700 mb-1">専門分野</label>
                <input type="text" name="specialty" id="specialty" value="{{ old('specialty') }}"
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('specialty') border-red-500 @enderror"
                       placeholder="例: 経営戦略、IT、財務">
                @error('specialty')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">コンサルタントの専門分野を入力してください。</p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    キャンセル
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    登録する
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
