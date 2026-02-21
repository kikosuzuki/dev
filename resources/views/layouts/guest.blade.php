<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'YCSコンサルタント予約システム')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-xl px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">YCSコンサルタント予約システム</h1>
            <p class="text-gray-500 mt-2">Consultant Booking System</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg px-8 py-10 sm:px-12 sm:py-12">
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="list-disc list-inside text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</body>
</html>
