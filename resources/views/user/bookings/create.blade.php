@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ url()->previous() }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition mb-4">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            戻る
        </a>
        <h1 class="text-2xl font-bold text-gray-900">予約確認</h1>
        <p class="mt-1 text-gray-600">予約内容を確認して送信してください。</p>
    </div>

    @if (session('error'))
        <div class="mb-6 bg-red-50 border border-red-300 text-red-700 rounded-md p-4">
            <p class="text-sm">{{ session('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-300 text-red-700 rounded-md p-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- スケジュール詳細 --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">予約詳細</h2>

        <div class="flex items-center space-x-4 mb-6">
            @if ($schedule->consultant->consultantProfile && $schedule->consultant->consultantProfile->photo)
                <img src="{{ Storage::disk('public')->url($schedule->consultant->consultantProfile->photo) }}" alt="{{ $schedule->consultant->name }}" class="w-16 h-16 rounded-full object-cover">
            @else
                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-indigo-600 font-semibold text-xl">{{ mb_substr($schedule->consultant->name, 0, 1) }}</span>
                </div>
            @endif
            <div>
                <h3 class="text-base font-semibold text-gray-900">{{ $schedule->consultant->name }}</h3>
                @if ($schedule->consultant->consultantProfile)
                    <p class="text-sm text-gray-500">{{ $schedule->consultant->consultantProfile->specialty }}</p>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">日付</dt>
                <dd class="mt-1 text-base font-semibold text-gray-900">{{ $schedule->date->format('Y年m月d日') }}</dd>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">時間</dt>
                <dd class="mt-1 text-base font-semibold text-gray-900">{{ \Illuminate\Support\Str::substr($schedule->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($schedule->end_time, 0, 5) }}</dd>
            </div>
            @if ($schedule->consultant->consultantProfile)
                @if ($schedule->consultant->consultantProfile->average_rating > 0)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <dt class="text-sm font-medium text-gray-500">評価</dt>
                        <dd class="mt-1 flex items-center space-x-1">
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="text-base font-semibold text-gray-900">{{ number_format($schedule->consultant->consultantProfile->average_rating, 1) }}</span>
                            <span class="text-sm text-gray-500">({{ $schedule->consultant->consultantProfile->total_reviews }}件のレビュー)</span>
                        </dd>
                    </div>
                @endif
            @endif
        </dl>
    </div>

    {{-- 予約フォーム --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="{{ route('user.bookings.store') }}">
            @csrf
            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">備考・メッセージ（任意）</label>
                <textarea id="notes" name="notes" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    placeholder="コンサルタントへのメッセージや相談内容を入力してください。">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-4">
                <a href="{{ url()->previous() }}"
                    class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition text-sm font-medium">
                    キャンセル
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-sm font-medium">
                    予約する
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
