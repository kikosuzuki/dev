@extends('layouts.guest')

@section('content')
<h2 class="text-center text-2xl font-bold text-gray-900 mb-10">パスワード再設定</h2>

<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ $email }}">

    <div class="mb-6">
        <label for="password" class="block text-base font-medium text-gray-700 mb-2">新しいパスワード</label>
        <input id="password" type="password" name="password" required autofocus
            class="w-full px-4 py-4 text-lg border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="8文字以上で入力">
    </div>

    <div class="mb-8">
        <label for="password_confirmation" class="block text-base font-medium text-gray-700 mb-2">パスワード確認</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required
            class="w-full px-4 py-4 text-lg border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="もう一度入力">
    </div>

    <div class="mb-6">
        <button type="submit"
            class="w-full flex justify-center py-4 px-4 border border-transparent rounded-lg shadow-sm text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
            パスワードを再設定
        </button>
    </div>

    <div class="text-center">
        <a href="{{ route('login') }}" class="text-base text-indigo-600 hover:text-indigo-500">
            ログインに戻る
        </a>
    </div>
</form>
@endsection
