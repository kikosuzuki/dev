@extends('layouts.guest')

@section('content')
<h2 class="text-center text-2xl font-bold text-gray-900 mb-10">ログイン</h2>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-6">
        <label for="email" class="block text-base font-medium text-gray-700 mb-2">メールアドレス</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
            class="w-full px-4 py-4 text-lg border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="example@email.com">
    </div>

    <div class="mb-6">
        <label for="password" class="block text-base font-medium text-gray-700 mb-2">パスワード</label>
        <input id="password" type="password" name="password" required
            class="w-full px-4 py-4 text-lg border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="パスワードを入力">
    </div>

    <div class="mb-8 flex items-center">
        <input id="remember" type="checkbox" name="remember"
            class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
        <label for="remember" class="ml-2 block text-base text-gray-700">ログイン状態を保持</label>
    </div>

    <div class="mb-6">
        <button type="submit"
            class="w-full flex justify-center py-4 px-4 border border-transparent rounded-lg shadow-sm text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
            ログイン
        </button>
    </div>

    <div class="text-center">
        <a href="{{ route('register') }}" class="text-base text-indigo-600 hover:text-indigo-500">
            アカウント登録はこちら
        </a>
    </div>
</form>
@endsection
