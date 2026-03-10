@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-gray-900">空き予約一覧</h1>
        <a href="{{ route('admin.schedules.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            予約枠を追加
        </a>
    </div>

    {{-- フィルター --}}
    <div class="bg-white rounded-lg shadow p-4 mb-8">
        <form method="GET" action="{{ route('admin.schedules.index') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="consultant" class="block text-sm font-medium text-gray-700 mb-1">コンサルタント</label>
                <select name="consultant" id="consultant" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">全て</option>
                    @foreach($consultants as $consultant)
                        <option value="{{ $consultant->id }}" {{ request('consultant') == $consultant->id ? 'selected' : '' }}>{{ $consultant->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 transition">
                    検索
                </button>
                <a href="{{ route('admin.schedules.index') }}" class="ml-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 transition">
                    リセット
                </a>
            </div>
        </form>
    </div>

    {{-- スケジュール一覧 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">予約可能な時間枠 ({{ $schedules->total() }}件)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日付</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">時間</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">コンサルタント</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">専門分野</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($schedules as $schedule)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $schedule->date->format('Y/m/d') }}
                                <span class="text-xs text-gray-500 ml-1">
                                    ({{ ['日','月','火','水','木','金','土'][$schedule->date->dayOfWeek] }})
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $schedule->consultant->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $schedule->consultant->consultantProfile?->specialty ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                予約可能な時間枠がありません
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($schedules->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $schedules->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
