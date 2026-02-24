@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.schedules.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
            &larr; 空き予約一覧に戻る
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-bold text-gray-900 mb-6">予約枠の追加</h1>

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.schedules.store') }}">
            @csrf

            {{-- コンサルタント選択 --}}
            <div class="mb-5">
                <label for="consultant_id" class="block text-sm font-medium text-gray-700 mb-1">コンサルタント <span class="text-red-500">*</span></label>
                <select name="consultant_id" id="consultant_id" required
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">選択してください</option>
                    @foreach($consultants as $consultant)
                        <option value="{{ $consultant->id }}" {{ old('consultant_id') == $consultant->id ? 'selected' : '' }}>
                            {{ $consultant->name }}
                            @if($consultant->consultantProfile?->specialty)
                                （{{ $consultant->consultantProfile->specialty }}）
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('consultant_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 日付 --}}
            <div class="mb-5">
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">日付 <span class="text-red-500">*</span></label>
                <input type="date" name="date" id="date" value="{{ old('date') }}" required min="{{ date('Y-m-d') }}"
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                @error('date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 時間 --}}
            <div class="grid grid-cols-2 gap-4 mb-5">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">開始時間 <span class="text-red-500">*</span></label>
                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @error('start_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">終了時間 <span class="text-red-500">*</span></label>
                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @error('end_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 送信ボタン --}}
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.schedules.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                    キャンセル
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition">
                    予約枠を追加
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
