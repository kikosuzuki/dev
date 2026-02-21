<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'YCSコンサルタント予約システム')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar overlay -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-40 lg:hidden" x-transition></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-800 transform transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-auto flex flex-col">
            <div class="flex items-center justify-between h-16 px-4 bg-slate-900">
                <span class="text-white font-bold text-lg">YCS予約システム</span>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto mt-4 px-3 space-y-1">
                @if(auth()->user()->isUser())
                    <a href="{{ route('user.dashboard') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('user.dashboard') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        ダッシュボード
                    </a>
                    <a href="{{ route('user.consultants.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('user.consultants.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        コンサルタント検索
                    </a>
                    <a href="{{ route('user.bookings.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('user.bookings.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        予約一覧
                    </a>
                    <a href="{{ route('user.schedules.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('user.schedules.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        空き予約一覧
                    </a>
                    <a href="{{ route('user.favorites.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('user.favorites.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        お気に入り
                    </a>
                    <a href="{{ route('user.profile.edit') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('user.profile.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        マイページ
                    </a>
                @elseif(auth()->user()->isConsultant())
                    <a href="{{ route('consultant.dashboard') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('consultant.dashboard') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        ダッシュボード
                    </a>
                    <a href="{{ route('consultant.schedules.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('consultant.schedules.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        スケジュール管理
                    </a>
                    <a href="{{ route('consultant.bookings.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('consultant.bookings.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        予約管理
                    </a>
                    <a href="{{ route('consultant.profile.edit') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('consultant.profile.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        プロフィール設定
                    </a>
                @elseif(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        ダッシュボード
                    </a>
                    <a href="{{ route('admin.bookings.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('admin.bookings.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        予約一覧
                    </a>
                    <a href="{{ route('admin.schedules.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('admin.schedules.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        空き予約一覧
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        ユーザー管理
                    </a>
                    <a href="{{ route('admin.stats.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('admin.stats.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        コンサルタント統計
                    </a>
                    <a href="{{ route('admin.user-stats.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('admin.user-stats.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        ユーザー統計
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg {{ request()->routeIs('admin.settings.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        システム設定
                    </a>
                @endif
            </nav>
            <div class="border-t border-slate-700 p-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-xs font-bold">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-400">{{ auth()->user()->isAdmin() ? '管理者' : (auth()->user()->isConsultant() ? 'コンサルタント' : 'ユーザー') }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm border-b border-gray-200 h-16 flex items-center justify-between px-4 sm:px-6">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-800 hidden sm:block">@yield('page_title', '')</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-700">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-red-600">ログアウト</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700" x-data="{ show: true }" x-show="show">
                        {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <ul class="list-disc list-inside text-sm text-red-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
