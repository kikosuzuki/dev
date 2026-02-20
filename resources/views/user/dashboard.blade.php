@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- ウェルカムメッセージ --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">ようこそ、{{ auth()->user()->name }}さん</h1>
        <p class="mt-1 text-gray-600">コンサルタント予約システムへようこそ。スケジュールを確認して予約を始めましょう。</p>
    </div>

    {{-- ナビゲーションリンク --}}
    <div class="mb-8 flex flex-wrap gap-4">
        <a href="{{ route('user.consultants.index') }}"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-sm font-medium">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            コンサルタントを探す
        </a>
        <a href="{{ route('user.bookings.index') }}"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition text-sm font-medium">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            予約一覧
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- カレンダー --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6" x-data="{
                currentMonth: {{ $month }},
                currentYear: {{ $year }},
                selectedDate: null,
                scheduleDates: {{ Js::from($scheduleDates->keys()) }},
                schedulesByDate: {{ Js::from($scheduleDates) }},

                get daysInMonth() {
                    return new Date(this.currentYear, this.currentMonth, 0).getDate();
                },
                get firstDayOfWeek() {
                    return new Date(this.currentYear, this.currentMonth - 1, 1).getDay();
                },
                get monthName() {
                    return this.currentYear + '年' + this.currentMonth + '月';
                },
                hasSlots(day) {
                    const dateStr = this.currentYear + '-' + String(this.currentMonth).padStart(2, '0') + '-' + String(day).padStart(2, '0');
                    return this.scheduleDates.includes(dateStr);
                },
                getDateString(day) {
                    return this.currentYear + '-' + String(this.currentMonth).padStart(2, '0') + '-' + String(day).padStart(2, '0');
                },
                selectDate(day) {
                    const dateStr = this.getDateString(day);
                    if (this.hasSlots(day)) {
                        this.selectedDate = dateStr;
                    }
                },
                getSlots() {
                    if (!this.selectedDate || !this.schedulesByDate[this.selectedDate]) return [];
                    return this.schedulesByDate[this.selectedDate];
                },
                prevMonth() {
                    let m = this.currentMonth - 1;
                    let y = this.currentYear;
                    if (m < 1) { m = 12; y--; }
                    window.location.href = '{{ route('user.dashboard') }}?month=' + m + '&year=' + y;
                },
                nextMonth() {
                    let m = this.currentMonth + 1;
                    let y = this.currentYear;
                    if (m > 12) { m = 1; y++; }
                    window.location.href = '{{ route('user.dashboard') }}?month=' + m + '&year=' + y;
                }
            }">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">予約可能スケジュール</h2>
                    <div class="flex items-center space-x-4">
                        <button @click="prevMonth()" class="p-2 rounded-md hover:bg-gray-100 transition">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <span class="text-base font-medium text-gray-700" x-text="monthName"></span>
                        <button @click="nextMonth()" class="p-2 rounded-md hover:bg-gray-100 transition">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- 曜日ヘッダー --}}
                <div class="grid grid-cols-7 gap-1 mb-2">
                    @foreach (['日', '月', '火', '水', '木', '金', '土'] as $dayName)
                        <div class="text-center text-sm font-medium text-gray-500 py-2">{{ $dayName }}</div>
                    @endforeach
                </div>

                {{-- カレンダーグリッド --}}
                <div class="grid grid-cols-7 gap-1">
                    <template x-for="blank in firstDayOfWeek" :key="'blank-' + blank">
                        <div class="h-12"></div>
                    </template>
                    <template x-for="day in daysInMonth" :key="day">
                        <div
                            @click="selectDate(day)"
                            :class="{
                                'bg-indigo-600 text-white hover:bg-indigo-700': hasSlots(day) && selectedDate === getDateString(day),
                                'bg-indigo-100 text-indigo-700 hover:bg-indigo-200 cursor-pointer': hasSlots(day) && selectedDate !== getDateString(day),
                                'text-gray-400': !hasSlots(day)
                            }"
                            class="h-12 flex items-center justify-center rounded-md text-sm font-medium transition"
                        >
                            <span x-text="day"></span>
                        </div>
                    </template>
                </div>

                {{-- 選択日の予約可能枠 --}}
                <div x-show="selectedDate && getSlots().length > 0" x-cloak class="mt-6 border-t pt-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">
                        <span x-text="selectedDate"></span> の予約可能枠
                    </h3>
                    <div class="space-y-3">
                        <template x-for="slot in getSlots()" :key="slot.id">
                            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-4 border">
                                <div>
                                    <p class="text-sm font-medium text-gray-900" x-text="slot.consultant.consultant_profile ? slot.consultant.consultant_profile.specialty : ''"></p>
                                    <p class="text-sm text-gray-600" x-text="slot.consultant.name"></p>
                                    <p class="text-sm text-gray-500" x-text="slot.start_time.substring(0, 5) + ' - ' + slot.end_time.substring(0, 5)"></p>
                                </div>
                                <a :href="'{{ route('user.bookings.create', ':id') }}'.replace(':id', slot.id)"
                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition">
                                    予約する
                                </a>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- 今後の予約 --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">今後の予約</h2>

                @if ($upcomingBookings->isEmpty())
                    <p class="text-sm text-gray-500">予約はありません。</p>
                @else
                    <div class="space-y-4">
                        @foreach ($upcomingBookings as $booking)
                            <a href="{{ route('user.bookings.show', $booking) }}" class="block p-4 rounded-lg border hover:border-indigo-300 hover:bg-indigo-50 transition">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ $booking->consultant->name }}
                                    </span>
                                    @if ($booking->status === 'pending')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">保留中</span>
                                    @elseif ($booking->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">承認済み</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600">{{ $booking->booking_date->format('Y/m/d') }}</p>
                                <p class="text-sm text-gray-500">{{ \Illuminate\Support\Str::substr($booking->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($booking->end_time, 0, 5) }}</p>
                                @if ($booking->consultant->consultantProfile)
                                    <p class="text-xs text-gray-400 mt-1">{{ $booking->consultant->consultantProfile->specialty }}</p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ route('user.bookings.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">全ての予約を見る</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 注目のコンサルタント --}}
    @if ($featuredConsultants->isNotEmpty())
        <div class="mt-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">注目のコンサルタント</h2>
                <a href="{{ route('user.consultants.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">全て見る</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($featuredConsultants as $consultant)
                    <a href="{{ route('user.consultants.show', $consultant) }}" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center space-x-4 mb-4">
                                @if ($consultant->consultantProfile && $consultant->consultantProfile->photo)
                                    <img src="{{ Storage::url($consultant->consultantProfile->photo) }}" alt="{{ $consultant->name }}" class="w-12 h-12 rounded-full object-cover">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-indigo-600 font-semibold text-lg">{{ mb_substr($consultant->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $consultant->name }}</h3>
                                    @if ($consultant->consultantProfile)
                                        <p class="text-xs text-gray-500">{{ $consultant->consultantProfile->specialty }}</p>
                                    @endif
                                </div>
                            </div>
                            @if ($consultant->consultantProfile)
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $consultant->consultantProfile->bio }}</p>
                                <div class="flex items-center">
                                    <div class="flex items-center space-x-1">
                                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <span class="text-sm text-gray-600">{{ number_format($consultant->consultantProfile->average_rating, 1) }}</span>
                                        <span class="text-xs text-gray-400">({{ $consultant->consultantProfile->total_reviews }}件)</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
