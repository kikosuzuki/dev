@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">プロフィール設定</h1>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-300 text-green-700 rounded-md p-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-300 text-red-700 rounded-md p-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('consultant.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow divide-y divide-gray-200">
            {{-- Basic Information --}}
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">基本情報</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">氏名</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">電話番号</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            {{-- Profile Photo --}}
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">プロフィール写真</h2>
                <div class="flex items-center space-x-6">
                    @if($profile && $profile->photo)
                        <div class="shrink-0">
                            <img class="h-20 w-20 object-cover rounded-full" src="{{ asset('storage/' . $profile->photo) }}" alt="プロフィール写真">
                        </div>
                    @else
                        <div class="shrink-0 h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center">
                            <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    @endif
                    <div>
                        <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">写真を変更</label>
                        <input type="file" id="photo" name="photo" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG形式。最大2MB。</p>
                    </div>
                </div>
            </div>

            {{-- Professional Information --}}
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">専門情報</h2>
                <div class="space-y-6">
                    <div>
                        <label for="specialty" class="block text-sm font-medium text-gray-700 mb-1">専門分野</label>
                        <input type="text" id="specialty" name="specialty" value="{{ old('specialty', $profile->specialty ?? '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="例: キャリアコンサルティング">
                    </div>
                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">自己紹介</label>
                        <textarea id="bio" name="bio" rows="5"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="経歴や得意分野について記載してください">{{ old('bio', $profile->bio ?? '') }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="experience_years" class="block text-sm font-medium text-gray-700 mb-1">経験年数</label>
                            <input type="number" id="experience_years" name="experience_years" value="{{ old('experience_years', $profile->experience_years ?? '') }}" min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="10">
                        </div>
                    </div>
                    <div>
                        <label for="qualifications" class="block text-sm font-medium text-gray-700 mb-1">資格</label>
                        <input type="text" id="qualifications" name="qualifications"
                            value="{{ old('qualifications', $profile && $profile->qualifications ? implode(', ', $profile->qualifications) : '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="資格をカンマ区切りで入力（例: MBA, 中小企業診断士, PMP）">
                        <p class="mt-1 text-xs text-gray-500">複数の場合はカンマ（,）で区切ってください。</p>
                    </div>
                    <div>
                        <label for="languages" class="block text-sm font-medium text-gray-700 mb-1">対応言語</label>
                        <input type="text" id="languages" name="languages"
                            value="{{ old('languages', $profile && $profile->languages ? implode(', ', $profile->languages) : '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="対応言語をカンマ区切りで入力（例: 日本語, 英語, 中国語）">
                        <p class="mt-1 text-xs text-gray-500">複数の場合はカンマ（,）で区切ってください。</p>
                    </div>
                </div>
            </div>

            {{-- Meeting & Reminder Settings --}}
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">ミーティング・リマインド設定</h2>
                <div class="space-y-6">
                    <div>
                        <label for="meeting_url" class="block text-sm font-medium text-gray-700 mb-1">ミーティングURL（Zoom等）</label>
                        <input type="url" id="meeting_url" name="meeting_url" value="{{ old('meeting_url', $profile->meeting_url ?? '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="https://zoom.us/j/1234567890">
                        <p class="mt-1 text-xs text-gray-500">リマインド通知に含まれるミーティングリンクです。Zoom、Google Meet等のURLを入力してください。</p>
                    </div>
                    <div>
                        <label for="reminder_message" class="block text-sm font-medium text-gray-700 mb-1">カスタムリマインドメッセージ</label>
                        <textarea id="reminder_message" name="reminder_message" rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="リマインド通知に追加したいメッセージを入力してください（例: 事前にZoomアプリの更新をお願いします）">{{ old('reminder_message', $profile->reminder_message ?? '') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">空欄の場合はシステムのデフォルトメッセージが使用されます。</p>
                    </div>
                </div>
            </div>

            {{-- Settings --}}
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">設定</h2>
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <label for="auto_approve" class="text-sm font-medium text-gray-700">自動承認</label>
                            <p class="text-xs text-gray-500">有効にすると、予約リクエストが自動的に承認されます。</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="auto_approve" value="0">
                            <input type="checkbox" id="auto_approve" name="auto_approve" value="1"
                                {{ old('auto_approve', $profile->auto_approve ?? false) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                    <div>
                        <label for="notification_channel" class="block text-sm font-medium text-gray-700 mb-1">通知方法</label>
                        <select id="notification_channel" name="notification_channel"
                            class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="email" {{ old('notification_channel', $user->notification_channel) === 'email' ? 'selected' : '' }}>メール</option>
                            <option value="line" {{ old('notification_channel', $user->notification_channel) === 'line' ? 'selected' : '' }}>LINE</option>
                            <option value="both" {{ old('notification_channel', $user->notification_channel) === 'both' ? 'selected' : '' }}>メール + LINE</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    保存する
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
