<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KeyCompare — Best prices across stores')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">K</div>
                <span class="font-semibold text-lg">KeyCompare</span>
            </a>
            <form action="{{ route('products.index') }}" method="get" class="flex-1 max-w-xl mx-6">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products..."
                           class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </form>
            <a href="/admin" class="text-sm text-slate-600 hover:text-slate-900 flex items-center gap-1">
                <i class="fas fa-cog"></i> Admin
            </a>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-6">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 mt-12 py-6 bg-white">
        <div class="container mx-auto px-4 text-center text-sm text-slate-500">
            © {{ date('Y') }} KeyCompare · Prices from {{ \App\Models\Offer::count() }} offers across {{ \App\Models\Store::count() }} stores
        </div>
    </footer>
</body>
</html>
