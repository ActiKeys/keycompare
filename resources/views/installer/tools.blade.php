@extends('installer.layout')

@section('title', 'System Tools')

@section('content')
    <h1 class="text-2xl font-bold mb-2">System tools</h1>
    <p class="text-slate-600 mb-6">Troubleshoot and fix common issues. Available anytime, even after installation.</p>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg p-3 mb-4 text-sm">
        ✓ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4 text-sm">
        ✗ {{ session('error') }}
    </div>
    @endif

    <h2 class="text-lg font-semibold mb-3">System status</h2>
    <div class="space-y-2 mb-6">
        @foreach($checks as $key => $check)
        <div class="flex items-center gap-3 p-3 rounded-lg {{ $check['ok'] ? 'bg-emerald-50' : 'bg-amber-50' }}">
            <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0
                {{ $check['ok'] ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white' }} text-sm">
                {{ $check['ok'] ? '✓' : '!' }}
            </div>
            <div class="flex-1">
                <div class="font-medium text-sm">{{ $check['label'] }}</div>
                @if(isset($check['value']))
                <div class="text-xs text-slate-600">{{ $check['value'] }}</div>
                @endif
                @if(isset($check['required']) && !$check['ok'])
                <div class="text-xs text-amber-700 mt-0.5">Required: {{ $check['required'] }}</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <h2 class="text-lg font-semibold mb-3">Quick actions</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <form method="post" action="{{ route('installer.tool') }}">
            @csrf
            <input type="hidden" name="action" value="fix_permissions">
            <button type="submit" class="w-full text-left p-3 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-indigo-300 transition-colors">
                <div class="font-medium text-sm">🔧 Fix permissions</div>
                <div class="text-xs text-slate-500">Set 0755/0644 on storage, cache, .env</div>
            </button>
        </form>

        <form method="post" action="{{ route('installer.tool') }}">
            @csrf
            <input type="hidden" name="action" value="create_storage_link">
            <button type="submit" class="w-full text-left p-3 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-indigo-300 transition-colors">
                <div class="font-medium text-sm">📁 Create storage link</div>
                <div class="text-xs text-slate-500">Link public/storage → storage/app/public</div>
            </button>
        </form>

        <form method="post" action="{{ route('installer.tool') }}">
            @csrf
            <input type="hidden" name="action" value="clear_cache">
            <button type="submit" class="w-full text-left p-3 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-indigo-300 transition-colors">
                <div class="font-medium text-sm">🧹 Clear all caches</div>
                <div class="text-xs text-slate-500">Config, route, view, app cache</div>
            </button>
        </form>

        <form method="post" action="{{ route('installer.tool') }}">
            @csrf
            <input type="hidden" name="action" value="migrate">
            <button type="submit" class="w-full text-left p-3 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-indigo-300 transition-colors">
                <div class="font-medium text-sm">🗄️ Run migrations</div>
                <div class="text-xs text-slate-500">Apply pending database migrations</div>
            </button>
        </form>

        @if($checks['installed']['ok'])
        <form method="post" action="{{ route('installer.tool') }}" onsubmit="return confirm('This will reset the installation and let you re-run the installer. Continue?')">
            @csrf
            <input type="hidden" name="action" value="reset_installation">
            <button type="submit" class="w-full text-left p-3 border border-amber-200 rounded-lg bg-amber-50 hover:bg-amber-100 transition-colors">
                <div class="font-medium text-sm text-amber-900">⚠️ Reset installation</div>
                <div class="text-xs text-amber-700">Remove lock file, re-run installer</div>
            </button>
        </form>
        @endif
    </div>

    <p class="mt-6 text-xs text-slate-500 text-center">
        <a href="{{ url('/') }}" class="text-indigo-600">← Back to site</a>
        @if(!$checks['installed']['ok'])
        · <a href="{{ route('installer.welcome') }}" class="text-indigo-600">Continue installation →</a>
        @endif
    </p>
@endsection
