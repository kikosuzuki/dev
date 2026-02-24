@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
            <h2 class="text-lg font-semibold text-gray-900">{{ $year }}年{{ $month }}月</h2>
            <a href="{{ route('consultant.schedules.index', ['month' => $month == 12 ? 1 : $month + 1, 'year' => $month == 12 ? $year + 1 : $year]) }}"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                翌月
                <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>

    {{-- Calendar Grid --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
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
                                @endphp
                                <div class="flex items-center justify-between text-xs p-1 rounded
                                    {{ $isBooked ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    <span>
                                        {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                    </span>
                                    @if($isBooked)
                                        <span class="font-medium">予約済</span>
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
</div>
@endsection
