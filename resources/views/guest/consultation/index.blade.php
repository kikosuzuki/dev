@extends('layouts.consultation')

@section('title', '個別相談 - 空き日程一覧')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">個別相談の空き日程</h1>
    <p class="mt-2 text-gray-600">ご都合の良い日時をお選びください。ご予約にアカウント登録は不要です。</p>
</div>

{{-- ビュー切り替え --}}
<div class="flex items-center justify-between mb-6">
    <div class="inline-flex rounded-md shadow-sm" role="group">
        <a href="{{ route('consultation.index', array_merge(request()->except('view', 'page'), ['view' => 'list'])) }}"
            class="inline-flex items-center px-4 py-2 text-sm font-medium border {{ $view === 'list' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }} rounded-l-md transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
            </svg>
            一覧
        </a>
        <a href="{{ route('consultation.index', array_merge(request()->except('view', 'page'), ['view' => 'calendar'])) }}"
            class="inline-flex items-center px-4 py-2 text-sm font-medium border-t border-b border-r {{ $view === 'calendar' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }} rounded-r-md transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            カレンダー
        </a>
    </div>
</div>

@if($view === 'list')
    {{-- フィルター --}}
    <div class="bg-white rounded-lg shadow p-4 mb-8">
        <form method="GET" action="{{ route('consultation.index') }}" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="view" value="list">
            @if($intro)
                <input type="hidden" name="intro" value="{{ $intro }}">
            @endif
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
                <a href="{{ route('consultation.index', array_merge(['view' => 'list'], $intro ? ['intro' => $intro] : [])) }}" class="ml-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 transition">
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
                                <a href="{{ route('consultation.create', array_merge(['schedule' => $schedule->id], $intro ? ['intro' => $intro] : [])) }}"
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
@else
    {{-- カレンダービュー --}}
    @php
        $firstDay = \Carbon\Carbon::create($year, $month, 1);
        $daysInMonth = $firstDay->daysInMonth;
        $startDayOfWeek = ($firstDay->dayOfWeek + 6) % 7;
        $today = \Carbon\Carbon::today();
        $prevMonth = $month == 1 ? 12 : $month - 1;
        $prevYear = $month == 1 ? $year - 1 : $year;
        $nextMonth = $month == 12 ? 1 : $month + 1;
        $nextYear = $month == 12 ? $year + 1 : $year;
    @endphp

    {{-- 月ナビゲーション --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 flex items-center justify-between">
            <a href="{{ route('consultation.index', array_merge(request()->except('year', 'month', 'page'), ['view' => 'calendar', 'year' => $prevYear, 'month' => $prevMonth])) }}"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                前月
            </a>
            <h2 class="text-lg font-semibold text-gray-900">{{ $year }}年{{ $month }}月</h2>
            <a href="{{ route('consultation.index', array_merge(request()->except('year', 'month', 'page'), ['view' => 'calendar', 'year' => $nextYear, 'month' => $nextMonth])) }}"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                翌月
                <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>

    {{-- カレンダーグリッド --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200">
            @foreach(['月', '火', '水', '木', '金', '土', '日'] as $dayLabel)
                <div class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                    {{ $dayLabel }}
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-7">
            @for($i = 0; $i < $startDayOfWeek; $i++)
                <div class="min-h-[120px] border-b border-r border-gray-200 bg-gray-50"></div>
            @endfor

            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $currentDate = \Carbon\Carbon::create($year, $month, $day);
                    $dateKey = $currentDate->format('Y-m-d');
                    $slots = $calendarSchedules[$dateKey] ?? collect();
                    $isToday = $currentDate->isSameDay($today);
                    $isPast = $currentDate->lt($today);
                @endphp
                <div class="min-h-[120px] border-b border-r border-gray-200 p-2 {{ $isToday ? 'bg-emerald-50' : ($isPast ? 'bg-gray-50' : '') }}">
                    <div class="text-sm font-medium {{ $isToday ? 'text-emerald-600' : ($isPast ? 'text-gray-400' : 'text-gray-900') }} mb-1">
                        {{ $day }}
                    </div>
                    @if($slots->isNotEmpty())
                        <div class="space-y-1">
                            @foreach($slots as $slot)
                                <a href="{{ route('consultation.create', array_merge(['schedule' => $slot->id], $intro ? ['intro' => $intro] : [])) }}"
                                    class="block text-xs p-1.5 rounded bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endfor

            @php
                $totalCells = $startDayOfWeek + $daysInMonth;
                $remainingCells = $totalCells % 7 === 0 ? 0 : 7 - ($totalCells % 7);
            @endphp
            @for($i = 0; $i < $remainingCells; $i++)
                <div class="min-h-[120px] border-b border-r border-gray-200 bg-gray-50"></div>
            @endfor
        </div>
    </div>
@endif
@endsection
