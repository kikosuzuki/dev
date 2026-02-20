@extends('layouts.guest')

@section('content')
<h2 class="text-center text-2xl font-bold text-gray-900 mb-8">アカウント登録</h2>

<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="mb-5">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">名前</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
            class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="お名前を入力">
    </div>

    <div class="mb-5">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">メールアドレス</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required
            class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="example@email.com">
    </div>

    <div class="mb-5">
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">電話番号（任意）</label>
        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}"
            class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="090-1234-5678">
    </div>

    <div class="mb-5">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">パスワード</label>
        <input id="password" type="password" name="password" required
            class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="パスワードを入力">
    </div>

    <div class="mb-6">
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">パスワード（確認）</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required
            class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="パスワードを再入力">
    </div>

    <div class="mb-5">
        <button type="submit"
            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
            登録する
        </button>
    </div>

    <div class="text-center">
        <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
            ログインはこちら
        </a>
    </div>
</form>
@endsection
