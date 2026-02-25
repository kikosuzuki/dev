@extends('layouts.guest')

@section('content')
<h2 class="text-center text-2xl font-bold text-gray-900 mb-4">パスワード再発行</h2>
<p class="text-center text-sm text-gray-500 mb-8">
    登録済みのメールアドレスを入力してください。<br>パスワードリセット用のリンクをお送りします。
</p>

@if(session('status'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-sm text-green-700">{{ session('status') }}</p>
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="mb-6">
        <label for="email" class="block text-base font-medium text-gray-700 mb-2">メールアドレス</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
            class="w-full px-4 py-4 text-lg border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="example@email.com">
    </div>

    <div class="mb-6">
        <button type="submit"
            class="w-full flex justify-center py-4 px-4 border border-transparent rounded-lg shadow-sm text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
            リセットリンクを送信
        </button>
    </div>

    <div class="text-center">
        <a href="{{ route('login') }}" class="text-base text-indigo-600 hover:text-indigo-500">
            ログインに戻る
        </a>
    </div>
</form>
@endsection
