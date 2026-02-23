@extends('layouts.consultation')

@section('title', '個別相談 - 受付停止中')

@section('content')
<div class="max-w-lg mx-auto text-center py-16">
    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <h1 class="mt-6 text-2xl font-bold text-gray-900">現在、予約の受付を停止しております</h1>
    <p class="mt-4 text-gray-600">申し訳ございませんが、現在新規のご予約を受け付けておりません。<br>受付再開まで今しばらくお待ちください。</p>
</div>
@endsection
