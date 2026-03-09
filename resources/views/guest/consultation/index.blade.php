@extends('layouts.consultation')

@section('title', '個別相談 - 空き日程一覧')

@section('content')
<div class="mb-4 sm:mb-8">
    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">個別相談の空き日程</h1>
    <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">ご都合の良い日時をお選びください。</p>
</div>

{{-- ビュー切り替え --}}
<div class="flex items-center justify-between mb-4 sm:mb-6">
    <div class="inline-flex rounded-md shadow-sm" role="group">
        <a href="{{ route('consultation.index', array_merge(request()->except('view', 'page'), ['view' => 'list'])) }}"
            class="inline-flex items-center px-3 sm:px-4 py-2 text-sm font-medium border {{ $view === 'list' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }} rounded-l-md transition">
            <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
            </svg>
            一覧
        </a>
        <a href="{{ route('consultation.index', array_merge(request()->except('view', 'page'), ['view' => 'calendar'])) }}"
            class="inline-flex items-center px-3 sm:px-4 py-2 text-sm font-medium border-t border-b border-r {{ $view === 'calendar' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }} rounded-r-md transition">
            <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            カレンダー
        </a>
    </div>
</div>

@if($view === 'list')
    {{-- フィルター --}}
    <div class="bg-white rounded-lg shadow p-3 sm:p-4 mb-4 sm:mb-8">
        <form method="GET" action="{{ route('consultation.index') }}" class="flex flex-wrap items-end gap-2 sm:gap-4">
            <input type="hidden" name="view" value="list">
            @if($intro)
                <input type="hidden" name="intro" value="{{ $intro }}">
            @endif
            <div class="flex-1 min-w-[140px]">
                <label for="consultant" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">コンサルタント</label>
                <select name="consultant" id="consultant" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    <option value="">全て</option>
                    @foreach($consultants as $consultant)
                        <option value="{{ $consultant->id }}" {{ request('consultant') == $consultant->id ? 'selected' : '' }}>{{ $consultant->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[120px]">
                <label for="date_from" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">開始日</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
            </div>
            <div class="flex-1 min-w-[120px]">
                <label for="date_to" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">終了日</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center px-3 sm:px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-emerald-700 transition">
                    検索
                </button>
                <a href="{{ route('consultation.index', array_merge(['view' => 'list'], $intro ? ['intro' => $intro] : [])) }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 transition">
                    リセット
                </a>
            </div>
        </form>
    </div>

    {{-- スケジュール一覧 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">予約可能な時間枠 ({{ $schedules->total() }}件)</h2>
        </div>

        {{-- デスクトップ: テーブル表示 --}}
        <div class="hidden sm:block overflow-x-auto">
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

        {{-- モバイル: カード表示 --}}
        <div class="sm:hidden divide-y divide-gray-200">
            @forelse($schedules as $schedule)
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $schedule->date->format('n/j') }}<span class="text-xs text-gray-500 ml-0.5">({{ ['日','月','火','水','木','金','土'][$schedule->date->dayOfWeek] }})</span>
                        </div>
                        <div class="text-sm text-gray-600 mt-0.5">
                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                        </div>
                    </div>
                    <a href="{{ route('consultation.create', array_merge(['schedule' => $schedule->id], $intro ? ['intro' => $intro] : [])) }}"
                        class="ml-3 flex-shrink-0 inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-md hover:bg-emerald-700 transition">
                        予約する
                    </a>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-sm text-gray-500">
                    現在予約可能な時間枠がありません。
                </div>
            @endforelse
        </div>

        @if($schedules->hasPages())
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200">
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

    {{-- コンサルタントフィルター --}}
    <div class="bg-white rounded-lg shadow p-3 sm:p-4 mb-4 sm:mb-6">
        <form method="GET" action="{{ route('consultation.index') }}" class="flex flex-wrap items-end gap-2 sm:gap-4">
            <input type="hidden" name="view" value="calendar">
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            @if($intro)
                <input type="hidden" name="intro" value="{{ $intro }}">
            @endif
            <div class="flex-1 min-w-[140px]">
                <label for="consultant_calendar" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">コンサルタント</label>
                <select name="consultant" id="consultant_calendar" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    <option value="">全て</option>
                    @foreach($consultants as $consultant)
                        <option value="{{ $consultant->id }}" {{ request('consultant') == $consultant->id ? 'selected' : '' }}>{{ $consultant->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center px-3 sm:px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-emerald-700 transition">
                    検索
                </button>
                <a href="{{ route('consultation.index', array_merge(['view' => 'calendar', 'year' => $year, 'month' => $month], $intro ? ['intro' => $intro] : [])) }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 transition">
                    リセット
                </a>
            </div>
        </form>
    </div>

    {{-- 月ナビゲーション --}}
    <div class="bg-white rounded-lg shadow mb-4 sm:mb-6">
        <div class="px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
            <a href="{{ route('consultation.index', array_merge(request()->except('year', 'month', 'page'), ['view' => 'calendar', 'year' => $prevYear, 'month' => $prevMonth])) }}"
                class="inline-flex items-center px-2 sm:px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span class="hidden sm:inline ml-1">前月</span>
            </a>
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">{{ $year }}年{{ $month }}月</h2>
            <a href="{{ route('consultation.index', array_merge(request()->except('year', 'month', 'page'), ['view' => 'calendar', 'year' => $nextYear, 'month' => $nextMonth])) }}"
                class="inline-flex items-center px-2 sm:px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                <span class="hidden sm:inline mr-1">翌月</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>

    {{-- デスクトップ: カレンダーグリッド --}}
    <div class="hidden sm:block bg-white rounded-lg shadow overflow-hidden">
        <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200">
            @foreach(['月', '火', '水', '木', '金', '土', '日'] as $dayLabel)
                <div class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                    {{ $dayLabel }}
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-7">
            @for($i = 0; $i < $startDayOfWeek; $i++)
                <div class="min-h-[100px] border-b border-r border-gray-200 bg-gray-50"></div>
            @endfor

            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $currentDate = \Carbon\Carbon::create($year, $month, $day);
                    $dateKey = $currentDate->format('Y-m-d');
                    $slots = $calendarSchedules[$dateKey] ?? collect();
                    $isToday = $currentDate->isSameDay($today);
                    $isPast = $currentDate->lt($today);
                @endphp
                <div class="min-h-[100px] border-b border-r border-gray-200 p-1.5 {{ $isToday ? 'bg-emerald-50' : ($isPast ? 'bg-gray-50' : '') }}">
                    <div class="text-sm font-medium {{ $isToday ? 'text-emerald-600' : ($isPast ? 'text-gray-400' : 'text-gray-900') }} mb-1">
                        {{ $day }}
                    </div>
                    @if($slots->isNotEmpty())
                        <div class="space-y-1">
                            @foreach($slots as $slot)
                                <a href="{{ route('consultation.create', array_merge(['schedule' => $slot->id], $intro ? ['intro' => $intro] : [])) }}"
                                    class="block text-xs px-1.5 py-1 rounded bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition font-medium text-center">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}<span class="text-emerald-400 mx-0.5">-</span>{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
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
                <div class="min-h-[100px] border-b border-r border-gray-200 bg-gray-50"></div>
            @endfor
        </div>
    </div>

    {{-- モバイル: 日付リスト形式 --}}
    <div class="sm:hidden bg-white rounded-lg shadow overflow-hidden">
        @php
            $hasAnySlots = false;
        @endphp
        @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $currentDate = \Carbon\Carbon::create($year, $month, $day);
                $dateKey = $currentDate->format('Y-m-d');
                $slots = $calendarSchedules[$dateKey] ?? collect();
                $isToday = $currentDate->isSameDay($today);
                $isPast = $currentDate->lt($today);
                $dayOfWeekLabel = ['日','月','火','水','木','金','土'][$currentDate->dayOfWeek];
            @endphp
            @if($slots->isNotEmpty())
                @php $hasAnySlots = true; @endphp
                <div class="border-b border-gray-200 {{ $isToday ? 'bg-emerald-50' : '' }}">
                    <div class="px-4 py-2 bg-gray-50 {{ $isToday ? '!bg-emerald-100' : '' }}">
                        <span class="text-sm font-semibold {{ $isToday ? 'text-emerald-700' : 'text-gray-900' }}">
                            {{ $currentDate->format('n/j') }}
                            <span class="text-xs font-normal {{ $isToday ? 'text-emerald-600' : 'text-gray-500' }} ml-0.5">({{ $dayOfWeekLabel }})</span>
                        </span>
                        @if($isToday)
                            <span class="ml-2 text-xs font-medium text-emerald-600">TODAY</span>
                        @endif
                    </div>
                    <div class="px-4 py-2 space-y-2">
                        @foreach($slots as $slot)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                </span>
                                <a href="{{ route('consultation.create', array_merge(['schedule' => $slot->id], $intro ? ['intro' => $intro] : [])) }}"
                                    class="inline-flex items-center px-4 py-1.5 bg-emerald-600 text-white text-sm font-medium rounded-md hover:bg-emerald-700 transition">
                                    予約する
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endfor

        @if(!$hasAnySlots)
            <div class="px-4 py-8 text-center text-sm text-gray-500">
                {{ $month }}月に予約可能な時間枠はありません。
            </div>
        @endif
    </div>
@endif

{{-- 日程調整リクエスト --}}
<div class="mt-6 text-center">
    <p class="text-sm text-gray-600 mb-3">ご希望の日時が見つからない場合は、メールでご相談ください。</p>
    <a href="mailto:marketing@cwa-ycs.com?subject={{ rawurlencode('日程調整のご相談') }}&body={{ rawurlencode("お世話になっております。\n\n下記の日程で相談を希望しております。\nご調整いただけますと幸いです。\n\n【お名前】\n\n【候補1】　月／日（　）00:00〜\n【候補2】　月／日（　）00:00〜\n【候補3】　月／日（　）00:00〜\n\n【ご相談内容】\n\nよろしくお願いいたします。") }}"
        class="inline-flex items-center px-5 py-2.5 bg-white border border-emerald-600 text-emerald-600 text-sm font-medium rounded-md hover:bg-emerald-50 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        ご希望の日時が見つからない場合はこちら
    </a>
</div>
@endsection
