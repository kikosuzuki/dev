@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">予約一覧</h1>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4">
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">期間</label>
                    <select name="period" class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" style="padding: 0.5rem 2rem 0.5rem 0.75rem;">
                        @foreach($periods as $key => $label)
                            <option value="{{ $key }}" {{ $period == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">コンサルタント</label>
                    <select name="consultant_id" class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" style="padding: 0.5rem 2rem 0.5rem 0.75rem;">
                        <option value="">全員</option>
                        @foreach($consultants as $c)
                            <option value="{{ $c->id }}" {{ $consultant_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ステータス</label>
                    <select name="status" class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" style="padding: 0.5rem 2rem 0.5rem 0.75rem;">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>全て</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>保留中</option>
                        <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>承認済</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">予約種別</label>
                    <select name="booking_type" class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" style="padding: 0.5rem 2rem 0.5rem 0.75rem;">
                        <option value="all" {{ $booking_type == 'all' ? 'selected' : '' }}>全て</option>
                        <option value="member" {{ $booking_type == 'member' ? 'selected' : '' }}>会員</option>
                        <option value="guest" {{ $booking_type == 'guest' ? 'selected' : '' }}>個別相談客</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        絞り込み
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">
                埋まっている予約
                <span class="ml-2 text-sm font-normal text-gray-500">（{{ $periods[$period] ?? '' }}）</span>
            </h2>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                {{ $bookings->total() }}件
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">予約ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">種別</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">予約者</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">コンサルタント</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">予約日</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">時間</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $booking->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($booking->isGuest())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">個別相談</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">会員</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>{{ $booking->bookerName() }}</div>
                                @if($booking->isGuest())
                                    <div class="text-xs text-gray-500">{{ $booking->guest_email }}</div>
                                    <div class="text-xs text-gray-500">{{ $booking->guest_phone }}</div>
                                @else
                                    <div class="text-xs text-gray-500">{{ $booking->user->email ?? '' }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $booking->consultant->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $booking->booking_date->format('Y/m/d (D)') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($booking->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">保留中</span>
                                @elseif($booking->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">承認済</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" x-data="{ emailOpen: false }">
                                @if($booking->isGuest() && $booking->guest_email)
                                    <button @click="emailOpen = true" type="button" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        メール
                                    </button>

                                    {{-- Guest Email Modal --}}
                                    <div x-show="emailOpen" x-cloak @keydown.escape.window="emailOpen = false" class="fixed inset-0 z-50 overflow-y-auto" x-transition>
                                        <div class="flex items-center justify-center min-h-screen px-4">
                                            <div class="fixed inset-0 bg-black/50" @click="emailOpen = false"></div>
                                            <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6 z-10">
                                                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $booking->guest_name }}さんにメール送信</h3>
                                                <p class="text-sm text-gray-500 mb-4">送信先: {{ $booking->guest_email }}</p>
                                                <form method="POST" action="{{ route('admin.bookings.guest-email.send', $booking) }}">
                                                    @csrf
                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">テンプレート</label>
                                                        <select onchange="if(this.value){ var t=JSON.parse(this.value); document.getElementById('email_subject_{{ $booking->id }}').value=t.s; document.getElementById('email_msg_{{ $booking->id }}').value=t.m; }" class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                                                            <option value="">テンプレートを選択...</option>
                                                            <option value="{{ e(json_encode(['s' => '【予約確認】個別相談のご予約について', 'm' => $booking->guest_name . "様\n\nご予約の確認をお願いいたします。\n\n■ 日時: " . $booking->booking_date->format('Y年m月d日') . ' ' . \Carbon\Carbon::parse($booking->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($booking->end_time)->format('H:i') . "\n\nご不明な点がございましたらお気軽にご連絡ください。"])) }}">予約確認</option>
                                                            <option value="{{ e(json_encode(['s' => '【リマインド】個別相談のご予約について', 'm' => $booking->guest_name . "様\n\n個別相談の予約日時が近づいてまいりました。\n\n■ 日時: " . $booking->booking_date->format('Y年m月d日') . ' ' . \Carbon\Carbon::parse($booking->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($booking->end_time)->format('H:i') . "\n\nご準備のほどよろしくお願いいたします。"])) }}">リマインド</option>
                                                            <option value="{{ e(json_encode(['s' => '【フォローアップ】個別相談について', 'm' => $booking->guest_name . "様\n\n先日の個別相談はいかがでしたでしょうか。\nご不明な点やご質問がございましたらお気軽にお問い合わせください。"])) }}">フォローアップ</option>
                                                            <option value="{{ e(json_encode(['s' => '【お知らせ】', 'm' => $booking->guest_name . "様\n\nお知らせがございます。\n詳細につきましては下記をご確認ください。\n\n"])) }}">お知らせ</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label for="email_subject_{{ $booking->id }}" class="block text-sm font-medium text-gray-700 mb-2">件名</label>
                                                        <input type="text" id="email_subject_{{ $booking->id }}" name="subject" required maxlength="200"
                                                               class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
                                                               placeholder="メールの件名を入力してください">
                                                    </div>
                                                    <div class="mb-4">
                                                        <label for="email_msg_{{ $booking->id }}" class="block text-sm font-medium text-gray-700 mb-2">本文</label>
                                                        <textarea id="email_msg_{{ $booking->id }}" name="message" rows="8" required
                                                                  class="block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
                                                                  placeholder="メッセージを入力してください..."></textarea>
                                                    </div>
                                                    <div class="flex justify-end space-x-3">
                                                        <button type="button" @click="emailOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">キャンセル</button>
                                                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">送信</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">該当する予約がありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
