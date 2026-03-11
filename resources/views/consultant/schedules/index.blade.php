@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ view: 'calendar' }">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-gray-900">スケジュール管理</h1>
        <a href="{{ route('consultant.schedules.create') }}"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            新規登録
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-300 text-green-700 rounded-md p-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-300 text-red-700 rounded-md p-4">
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 bg-orange-50 border border-orange-300 text-orange-700 rounded-md p-4">
            {{ session('warning') }}
        </div>
    @endif

    @if(session('skipped_slots') && count(session('skipped_slots')) > 0)
        <div class="mb-6 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-md p-4">
            <p class="font-medium mb-2">カレンダー重複によりスキップされた枠:</p>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach(session('skipped_slots') as $skipped)
                    <li>{{ $skipped['date'] }} {{ $skipped['time'] }} - {{ $skipped['reason'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Month Navigation --}}
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="px-6 py-4 flex items-center justify-between">
            <a href="{{ route('consultant.schedules.index', ['month' => $month == 1 ? 12 : $month - 1, 'year' => $month == 1 ? $year - 1 : $year]) }}"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                前月
            </a>
            <div class="flex items-center space-x-4">
                <h2 class="text-lg font-semibold text-gray-900">{{ $year }}年{{ $month }}月</h2>
                {{-- View Toggle Buttons --}}
                <div class="flex items-center border border-gray-300 rounded-md overflow-hidden">
                    <button @click="view = 'calendar'" type="button"
                        :class="view === 'calendar' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-500 hover:text-gray-700'"
                        class="p-2 transition-colors" title="カレンダー表示">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                    <button @click="view = 'list'" type="button"
                        :class="view === 'list' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-500 hover:text-gray-700'"
                        class="p-2 border-l border-gray-300 transition-colors" title="リスト表示">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <a href="{{ route('consultant.schedules.index', ['month' => $month == 12 ? 1 : $month + 1, 'year' => $month == 12 ? $year + 1 : $year]) }}"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                翌月
                <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>

    {{-- Calendar View --}}
    <div x-show="view === 'calendar'" class="bg-white rounded-lg shadow overflow-hidden">
        {{-- Day of week header --}}
        <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200">
            @foreach(['月', '火', '水', '木', '金', '土', '日'] as $dayLabel)
                <div class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                    {{ $dayLabel }}
                </div>
            @endforeach
        </div>

        {{-- Calendar days --}}
        @php
            $firstDay = \Carbon\Carbon::create($year, $month, 1);
            $daysInMonth = $firstDay->daysInMonth;
            // Monday = 0, Sunday = 6
            $startDayOfWeek = ($firstDay->dayOfWeek + 6) % 7;
            $today = \Carbon\Carbon::today();
        @endphp

        <div class="grid grid-cols-7">
            {{-- Empty cells for days before the 1st --}}
            @for($i = 0; $i < $startDayOfWeek; $i++)
                <div class="min-h-[120px] border-b border-r border-gray-200 bg-gray-50"></div>
            @endfor

            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $currentDate = \Carbon\Carbon::create($year, $month, $day);
                    $dateKey = $currentDate->format('Y-m-d');
                    $slots = $scheduleDates[$dateKey] ?? collect();
                    $isToday = $currentDate->isSameDay($today);
                @endphp
                <div class="min-h-[120px] border-b border-r border-gray-200 p-2 {{ $isToday ? 'bg-indigo-50' : '' }}">
                    <div class="text-sm font-medium {{ $isToday ? 'text-indigo-600' : 'text-gray-900' }} mb-1">
                        {{ $day }}
                    </div>
                    @if($slots->isNotEmpty())
                        <div class="space-y-1">
                            @foreach($slots as $slot)
                                @php
                                    $isBooked = $slot->isBooked();
                                    $isBlocked = $slot->isCalendarBlocked();
                                @endphp
                                <div class="flex items-center justify-between text-xs p-1 rounded
                                    {{ $isBooked ? 'bg-red-100 text-red-700' : ($isBlocked ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                    <span>
                                        {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                    </span>
                                    @if($isBooked)
                                        <span class="font-medium">予約済</span>
                                    @elseif($isBlocked)
                                        <span class="font-medium" title="{{ $slot->calendar_blocked_reason }}">重複</span>
                                    @else
                                        <form action="{{ route('consultant.schedules.destroy', $slot) }}" method="POST"
                                            onsubmit="return confirm('このスケジュール枠を削除しますか？');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700" title="削除">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endfor

            {{-- Empty cells for remaining days --}}
            @php
                $totalCells = $startDayOfWeek + $daysInMonth;
                $remainingCells = $totalCells % 7 === 0 ? 0 : 7 - ($totalCells % 7);
            @endphp
            @for($i = 0; $i < $remainingCells; $i++)
                <div class="min-h-[120px] border-b border-r border-gray-200 bg-gray-50"></div>
            @endfor
        </div>
    </div>

    {{-- List View --}}
    <div x-show="view === 'list'" x-cloak class="bg-white rounded-lg shadow overflow-hidden">
        @php
            $allSlots = collect($scheduleDates)->sortKeys();
            $hasAnySlots = $allSlots->flatten()->isNotEmpty();
        @endphp

        @if($hasAnySlots)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日付</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">時間</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($allSlots as $dateKey => $slots)
                        @php
                            $dateCarbon = \Carbon\Carbon::parse($dateKey);
                            $dayOfWeekLabels = ['日', '月', '火', '水', '木', '金', '土'];
                            $dayOfWeekLabel = $dayOfWeekLabels[$dateCarbon->dayOfWeek];
                            $isToday = $dateCarbon->isSameDay(\Carbon\Carbon::today());
                        @endphp
                        @foreach($slots as $slotIndex => $slot)
                            @php
                                $isBooked = $slot->isBooked();
                                $isBlocked = $slot->isCalendarBlocked();
                            @endphp
                            <tr class="{{ $isToday ? 'bg-indigo-50' : '' }}">
                                <td class="px-6 py-3 whitespace-nowrap text-sm">
                                    @if($slotIndex === 0)
                                        <span class="font-medium {{ $isToday ? 'text-indigo-600' : 'text-gray-900' }}">
                                            {{ $dateCarbon->format('m/d') }}（{{ $dayOfWeekLabel }}）
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    @if($isBooked)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">予約済</span>
                                    @elseif($isBlocked)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800" title="{{ $slot->calendar_blocked_reason }}">カレンダー重複</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">空き</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-right text-sm">
                                    @if(!$isBooked)
                                        <form action="{{ route('consultant.schedules.destroy', $slot) }}" method="POST"
                                            onsubmit="return confirm('このスケジュール枠を削除しますか？');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">
                                                削除
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="px-6 py-12 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-sm">この月にはスケジュール枠がありません。</p>
            </div>
        @endif
    </div>
</div>
@endsection
