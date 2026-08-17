@extends('installer.layout')

@section('content')
    <h1 class="text-2xl font-bold mb-2">Welcome to KeyCompare</h1>
    <p class="text-slate-600 mb-6">Let's get your price comparison site set up. We auto-fix what we can — no terminal access required.</p>

    @if(count($autoFixed ?? []) > 0)
    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-6">
        <h2 class="font-semibold text-emerald-900 mb-2 text-sm">✓ Auto-configured</h2>
        <ul class="text-xs text-emerald-800 space-y-0.5">
            @foreach($autoFixed as $action)
            <li>• {{ $action }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <h2 class="text-lg font-semibold mb-3">Requirements check</h2>
    <div class="space-y-2 mb-6">
        @foreach($requirements as $key => $req)
        <div class="flex items-start gap-3 p-3 rounded-lg {{ $req['ok'] ? 'bg-emerald-50' : 'bg-red-50' }}">
            <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5
                {{ $req['ok'] ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' }}">
                {{ $req['ok'] ? '✓' : '✗' }}
            </div>
            <div class="flex-1">
                <div class="font-medium text-sm {{ $req['ok'] ? 'text-emerald-900' : 'text-red-900' }}">{{ $req['label'] }}</div>
                @if(!$req['ok'] && isset($req['required']))
                <div class="text-xs text-red-700 mt-0.5">Required: {{ $req['required'] }}</div>
                @endif
                @if($key === 'storage' && !$req['ok'])
                <div class="text-xs text-red-700 mt-0.5">Use cPanel File Manager → right-click storage/ → Change Permissions → 0755</div>
                @endif
                @if($key === 'bootstrap' && !$req['ok'])
                <div class="text-xs text-red-700 mt-0.5">Create the folder via File Manager and set 0755</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if(!$allPassed)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 text-sm mb-4">
        <strong>Some requirements failed.</strong> You can still continue — the installer will try to work around them. But for best results, ask your hosting provider to enable missing PHP extensions.
        <br><br>
        <strong>Tip:</strong> After fixing permissions via File Manager, refresh this page to re-check.
    </div>
    @endif

    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
        <a href="{{ route('installer.tools') }}" class="text-sm text-slate-500 hover:text-slate-700">System tools →</a>
        @if($allPassed)
        <a href="{{ route('installer.database') }}" class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">
            Continue to database →
        </a>
        @else
        <a href="{{ route('installer.database') }}" class="px-6 py-3 bg-slate-700 text-white rounded-lg font-medium hover:bg-slate-800">
            Continue anyway →
        </a>
        @endif
    </div>
@endsection
