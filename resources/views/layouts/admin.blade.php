<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — KeyCompare</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-56 bg-slate-900 text-slate-100 flex flex-col">
            <div class="p-4 border-b border-slate-700">
                <a href="/admin" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-bold">K</div>
                    <div>
                        <div class="font-semibold text-sm">KeyCompare</div>
                        <div class="text-[10px] text-slate-400">Admin</div>
                    </div>
                </a>
            </div>

            <nav class="flex-1 py-2">
                <a href="{{ route('admin.products.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm {{ request()->routeIs('admin.products.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    <i class="fas fa-box w-4"></i> Products
                </a>
                <a href="{{ route('admin.media.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm {{ request()->routeIs('admin.media.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    <i class="fas fa-images w-4"></i> Media
                </a>
                <a href="{{ route('admin.import-logs.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">
                    <i class="fas fa-history w-4"></i> Import logs
                </a>
                <a href="/" target="_blank" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-400 hover:bg-slate-800 mt-4 border-t border-slate-700">
                    <i class="fas fa-external-link-alt w-4"></i> View site
                </a>
            </nav>
        </aside>

        <!-- Main -->
        <main class="flex-1 overflow-auto">
            <div class="p-6 max-w-7xl">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
