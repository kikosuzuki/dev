@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-gray-900">スケジュール登録</h1>
        <a href="{{ route('consultant.schedules.index') }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            戻る
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-300 text-red-700 rounded-md p-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-300 text-green-700 rounded-md p-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Single Day Registration --}}
    <div class="bg-white rounded-lg shadow mb-8" x-data="{
        slots: [{ start_time: '09:00', end_time: '10:00' }],
        addSlot() {
            this.slots.push({ start_time: '09:00', end_time: '10:00' });
        },
        removeSlot(index) {
            if (this.slots.length > 1) {
                this.slots.splice(index, 1);
            }
        }
    }">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">個別登録</h2>
            <p class="mt-1 text-sm text-gray-500">特定の日付にスケジュール枠を登録します。</p>
        </div>
        <form action="{{ route('consultant.schedules.store') }}" method="POST" class="p-6">
            @csrf

            <div class="mb-6">
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">日付</label>
                <input type="date" id="date" name="date" value="{{ old('date') }}" required
                    class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">時間枠</label>
                <template x-for="(slot, index) in slots" :key="index">
                    <div class="flex items-center space-x-3 mb-3">
                        <div>
                            <input type="time" :name="'slots[' + index + '][start_time]'" x-model="slot.start_time" required
                                class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <span class="text-gray-500">~</span>
                        <div>
                            <input type="time" :name="'slots[' + index + '][end_time]'" x-model="slot.end_time" required
                                class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <button type="button" @click="removeSlot(index)" x-show="slots.length > 1"
                            class="inline-flex items-center p-2 text-red-500 hover:text-red-700 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <div class="flex items-center space-x-4">
                <button type="button" @click="addSlot()"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    枠を追加
                </button>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    登録する
                </button>
            </div>
        </form>
    </div>

    {{-- Bulk Registration --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">一括登録</h2>
            <p class="mt-1 text-sm text-gray-500">期間と曜日を指定してスケジュール枠をまとめて登録します。</p>
        </div>
        <form action="{{ route('consultant.schedules.bulk') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">曜日</label>
                <div class="flex flex-wrap gap-3">
                    @php
                        $daysOfWeek = [
                            ['value' => 'mon', 'label' => '月'],
                            ['value' => 'tue', 'label' => '火'],
                            ['value' => 'wed', 'label' => '水'],
                            ['value' => 'thu', 'label' => '木'],
                            ['value' => 'fri', 'label' => '金'],
                            ['value' => 'sat', 'label' => '土'],
                            ['value' => 'sun', 'label' => '日'],
                        ];
                    @endphp
                    @foreach($daysOfWeek as $day)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="days_of_week[]" value="{{ $day['value'] }}"
                                {{ in_array($day['value'], old('days_of_week', [])) ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $day['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="skip_holidays" value="1"
                        {{ old('skip_holidays') ? 'checked' : '' }}
                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">祝日を除外する</span>
                </label>
                <p class="mt-1 text-xs text-gray-500">チェックすると、日本の祝日にはスケジュール枠を登録しません。</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="bulk_start_time" class="block text-sm font-medium text-gray-700 mb-1">開始時間</label>
                    <input type="time" id="bulk_start_time" name="start_time" value="{{ old('start_time', '09:00') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="bulk_end_time" class="block text-sm font-medium text-gray-700 mb-1">終了時間</label>
                    <input type="time" id="bulk_end_time" name="end_time" value="{{ old('end_time', '17:00') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="slot_duration" class="block text-sm font-medium text-gray-700 mb-1">1枠の時間（分）</label>
                    <input type="number" id="slot_duration" name="slot_duration" value="{{ old('slot_duration', 60) }}" min="15" step="15" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    一括登録する
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
