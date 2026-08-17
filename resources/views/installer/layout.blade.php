<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KeyCompare — Installation Wizard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen">
    <div class="max-w-3xl mx-auto py-12 px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-3 mb-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl">K</div>
                <span class="text-2xl font-bold text-slate-900">KeyCompare Setup</span>
            </div>
            <p class="text-slate-500">Installation wizard</p>
        </div>

        <!-- Stepper -->
        <div class="flex items-center justify-between mb-8 px-2">
            @php
                $steps = [
                    1 => 'Welcome',
                    2 => 'Database',
                    3 => 'Admin',
                    4 => 'Settings',
                    5 => 'Done',
                ];
                $current = $step ?? 1;
            @endphp
            @foreach($steps as $num => $label)
            <div class="flex items-center {{ $num < 5 ? 'flex-1' : '' }}">
                <div class="flex items-center gap-2 {{ $num <= $current ? 'text-indigo-600' : 'text-slate-300' }}">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm
                        {{ $num < $current ? 'bg-indigo-600 text-white' :
                           ($num == $current ? 'bg-indigo-600 text-white ring-4 ring-indigo-100' : 'bg-slate-200 text-slate-400') }}">
                        @if($num < $current)✓@else{{ $num }}@endif
                    </div>
                    <span class="text-sm font-medium {{ $num == $current ? 'text-slate-900' : '' }}">{{ $label }}</span>
                </div>
                @if($num < 5)
                <div class="flex-1 h-px mx-2 {{ $num < $current ? 'bg-indigo-600' : 'bg-slate-200' }}"></div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Content -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-8">
            @yield('content')
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">KeyCompare Laravel · v1.0</p>
    </div>
</body>
</html>
