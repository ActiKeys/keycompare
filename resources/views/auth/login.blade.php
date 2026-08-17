<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — KeyCompare Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-slate-900 to-indigo-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="inline-flex w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 items-center justify-center text-white font-bold text-2xl mb-3">K</div>
            <h1 class="text-2xl font-bold text-white">KeyCompare Admin</h1>
            <p class="text-slate-400 text-sm">Sign in to continue</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-8">
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4 text-sm">
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form method="post" action="{{ route('login.attempt') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Username or email</label>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" class="rounded">
                    <span>Remember me</span>
                </label>
                <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">
                    Sign in
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-slate-500 mt-6">
            <a href="{{ url('/') }}" class="hover:text-white">← Back to site</a>
        </p>
    </div>
</body>
</html>
