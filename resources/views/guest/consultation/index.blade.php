@extends('layouts.consultation')

@section('title', '個別相談 - 空き日程一覧')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">個別相談の空き日程</h1>
    <p class="mt-2 text-gray-600">ご都合の良い日時をお選びください。ご予約にアカウント登録は不要です。</p>
</div>

{{-- フィルター --}}
<div class="bg-white rounded-lg shadow p-4 mb-8">
    <form method="GET" action="{{ route('consultation.index') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                class="border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
        </div>
        <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                class="border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
        </div>
        <div>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-emerald-700 transition">
                検索
            </button>
            <a href="{{ route('consultation.index') }}" class="ml-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 transition">
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状態</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                予約可能
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('consultation.create', $schedule) }}"
                                class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-md hover:bg-emerald-700 transition">
                                予約する
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                            現在予約可能な時間枠がありません。
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
@endsection
