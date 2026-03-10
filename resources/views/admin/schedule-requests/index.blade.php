@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">日程リクエスト一覧</h1>

    {{-- ステータスフィルター --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.schedule-requests.index', ['status' => 'pending']) }}"
                class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium transition {{ $status === 'pending' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                未対応
                @if($pendingCount > 0)
                    <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-bold {{ $status === 'pending' ? 'bg-white text-indigo-600' : 'bg-red-100 text-red-700' }}">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('admin.schedule-requests.index', ['status' => 'processed']) }}"
                class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium transition {{ $status === 'processed' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                対応済み
            </a>
            <a href="{{ route('admin.schedule-requests.index', ['status' => 'all']) }}"
                class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium transition {{ $status === 'all' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                全て
            </a>
        </div>
    </div>

    {{-- リクエスト一覧 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">リクエスト ({{ $scheduleRequests->total() }}件)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">送信日時</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">お名前</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">メール</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">候補日時</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($scheduleRequests as $req)
                        <tr class="hover:bg-gray-50" x-data="{
                            modalOpen: false,
                            replyOpen: false,
                            bookingOpen: false,
                            duration: 60,
                            startTime: '',
                            endTime: '',
                            calcEnd() {
                                if (!this.startTime || !this.duration) return;
                                const [h, m] = this.startTime.split(':').map(Number);
                                const total = h * 60 + m + this.duration;
                                const endH = String(Math.floor(total / 60) % 24).padStart(2, '0');
                                const endM = String(total % 60).padStart(2, '0');
                                this.endTime = endH + ':' + endM;
                            }
                        }">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $req->created_at->format('Y/m/d H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $req->guest_name }}
                                @if($req->guest_phone)
                                    <div class="text-xs text-gray-500">{{ $req->guest_phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $req->guest_email }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="space-y-0.5">
                                    <div><span class="text-xs text-gray-500">1:</span> {{ $req->candidate_1 }}</div>
                                    @if($req->candidate_2)
                                        <div><span class="text-xs text-gray-500">2:</span> {{ $req->candidate_2 }}</div>
                                    @endif
                                    @if($req->candidate_3)
                                        <div><span class="text-xs text-gray-500">3:</span> {{ $req->candidate_3 }}</div>
                                    @endif
                                </div>
                                @if($req->message)
                                    <div class="mt-1 text-xs text-gray-500 truncate max-w-[200px]" title="{{ $req->message }}">
                                        {{ $req->message }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($req->isPending())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">未対応</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">対応済み</span>
                                    @if($req->admin_notes)
                                        <div class="mt-1 text-xs text-gray-500 truncate max-w-[150px]" title="{{ $req->admin_notes }}">
                                            {{ $req->admin_notes }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="replyOpen = true" type="button"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        メール
                                    </button>
                                    @if($req->isPending())
                                        <button @click="bookingOpen = true" type="button"
                                            class="inline-flex items-center px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded hover:bg-emerald-700 transition">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                            予約作成
                                        </button>
                                        <button @click="modalOpen = true" type="button"
                                            class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded hover:bg-indigo-700 transition">
                                            対応済み
                                        </button>
                                    @endif
                                </div>

                                {{-- メール返信モーダル --}}
                                <div x-show="replyOpen" x-cloak
                                    class="fixed inset-0 z-50 flex items-center justify-center"
                                    @keydown.escape.window="replyOpen = false">
                                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="replyOpen = false"></div>
                                    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 p-6 text-left" @click.stop>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-1">メール返信</h3>
                                        <p class="text-sm text-gray-500 mb-4">宛先: {{ $req->guest_email }}</p>
                                        <form method="POST" action="{{ route('admin.schedule-requests.reply', $req) }}">
                                            @csrf
                                            <div class="mb-4">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">件名</label>
                                                <input type="text" name="subject" value="日程調整のご連絡"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                            </div>
                                            <div class="mb-4">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">本文</label>
                                                <textarea name="body" rows="8"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ $req->guest_name }}様

日程調整のリクエストをいただきありがとうございます。

下記の日時で予約を承りました。

■ 日時: {{ $req->candidate_1 }}

よろしくお願いいたします。</textarea>
                                            </div>
                                            <div class="flex justify-end space-x-3">
                                                <button type="button" @click="replyOpen = false"
                                                    class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium">
                                                    キャンセル
                                                </button>
                                                <button type="submit"
                                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                                                    送信する
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                @if($req->isPending())
                                    {{-- 予約作成モーダル --}}
                                    <div x-show="bookingOpen" x-cloak
                                        class="fixed inset-0 z-50 flex items-center justify-center"
                                        @keydown.escape.window="bookingOpen = false">
                                        <div class="fixed inset-0 bg-black bg-opacity-50" @click="bookingOpen = false"></div>
                                        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 p-6 text-left" @click.stop>
                                            <h3 class="text-lg font-semibold text-gray-900 mb-1">予約作成</h3>
                                            <p class="text-sm text-gray-500 mb-4">{{ $req->guest_name }}様（{{ $req->guest_email }}）</p>
                                            <div class="mb-4 p-3 bg-gray-50 rounded-md text-sm text-gray-700">
                                                <div class="font-medium text-gray-900 mb-1">候補日時:</div>
                                                <div>1: {{ $req->candidate_1 }}</div>
                                                @if($req->candidate_2)<div>2: {{ $req->candidate_2 }}</div>@endif
                                                @if($req->candidate_3)<div>3: {{ $req->candidate_3 }}</div>@endif
                                            </div>
                                            <form method="POST" action="{{ route('admin.schedule-requests.create-booking', $req) }}">
                                                @csrf
                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">コンサルタント <span class="text-red-500">*</span></label>
                                                    <select name="consultant_id" required
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                                                        <option value="">選択してください</option>
                                                        @foreach($consultants as $consultant)
                                                            <option value="{{ $consultant->id }}">{{ $consultant->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">日付 <span class="text-red-500">*</span></label>
                                                    <input type="date" name="date" required min="{{ date('Y-m-d') }}"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                                                </div>
                                                <div class="grid grid-cols-3 gap-3 mb-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">開始時間 <span class="text-red-500">*</span></label>
                                                        <input type="time" name="start_time" x-model="startTime" @change="calcEnd()" required
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">所要時間</label>
                                                        <select x-model.number="duration" @change="calcEnd()"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                                                            <option value="30">30分</option>
                                                            <option value="45">45分</option>
                                                            <option value="60" selected>60分</option>
                                                            <option value="90">90分</option>
                                                            <option value="120">120分</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">終了時間 <span class="text-red-500">*</span></label>
                                                        <input type="time" name="end_time" x-model="endTime" required
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-500 mb-4">※ 枠が自動作成され、予約確定メールがゲストに送信されます</p>
                                                <div class="flex justify-end space-x-3">
                                                    <button type="button" @click="bookingOpen = false"
                                                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium">
                                                        キャンセル
                                                    </button>
                                                    <button type="submit"
                                                        class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm font-medium">
                                                        予約を作成
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- 対応済みモーダル --}}
                                    <div x-show="modalOpen" x-cloak
                                        class="fixed inset-0 z-50 flex items-center justify-center"
                                        @keydown.escape.window="modalOpen = false">
                                        <div class="fixed inset-0 bg-black bg-opacity-50" @click="modalOpen = false"></div>
                                        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6 text-left" @click.stop>
                                            <h3 class="text-lg font-semibold text-gray-900 mb-4">対応済みにする</h3>
                                            <form method="POST" action="{{ route('admin.schedule-requests.update-status', $req) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">メモ（任意）</label>
                                                    <textarea name="admin_notes" rows="3"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                                        placeholder="対応内容のメモ"></textarea>
                                                </div>
                                                <div class="flex justify-end space-x-3">
                                                    <button type="button" @click="modalOpen = false"
                                                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium">
                                                        キャンセル
                                                    </button>
                                                    <button type="submit"
                                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                                                        対応済みにする
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                該当するリクエストがありません
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($scheduleRequests->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $scheduleRequests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
