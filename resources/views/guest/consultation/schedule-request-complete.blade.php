@extends('layouts.consultation')

@section('title', 'リクエスト送信完了 - YCS個別相談')

@section('content')
<div class="max-w-lg mx-auto text-center py-8 sm:py-12">
    <div class="mb-6">
        <div class="mx-auto w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
    </div>

    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">リクエストを送信しました</h1>
    <p class="text-sm sm:text-base text-gray-600 mb-8">
        日程調整のリクエストを受け付けました。<br>
        担当者より折り返しご連絡いたしますので、しばらくお待ちください。
    </p>

    <a href="{{ route('consultation.index') }}"
        class="inline-flex items-center px-5 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-md hover:bg-emerald-700 transition">
        空き日程一覧に戻る
    </a>
</div>
@endsection
