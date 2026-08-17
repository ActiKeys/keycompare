@extends('installer.layout')

@section('content')
    <div class="text-center mb-6">
        <div class="inline-flex w-20 h-20 rounded-full bg-emerald-100 items-center justify-center text-emerald-600 text-4xl mb-4">
            ✓
        </div>
        <h1 class="text-2xl font-bold mb-2">Installation complete!</h1>
        <p class="text-slate-600">Your KeyCompare site is ready to use.</p>
    </div>

    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-6">
        <h2 class="font-semibold text-sm mb-3">Quick links</h2>
        <div class="space-y-2">
            <a href="{{ url('/') }}" target="_blank" class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-lg hover:border-indigo-300">
                <div>
                    <div class="font-medium text-sm">🏠 View homepage</div>
                    <div class="text-xs text-slate-500">{{ $app_url }}</div>
                </div>
                <span class="text-indigo-600">→</span>
            </a>
            <a href="{{ route('login') }}" target="_blank" class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-lg hover:border-indigo-300">
                <div>
                    <div class="font-medium text-sm">⚙️ Admin login</div>
                    <div class="text-xs text-slate-500">Manage products, media, and imports</div>
                </div>
                <span class="text-indigo-600">→</span>
            </a>
            <a href="{{ route('installer.tools') }}" class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-lg hover:border-indigo-300">
                <div>
                    <div class="font-medium text-sm">🔧 System tools</div>
                    <div class="text-xs text-slate-500">Clear cache, fix permissions, run migrations</div>
                </div>
                <span class="text-indigo-600">→</span>
            </a>
        </div>
    </div>

    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6">
        <h2 class="font-semibold text-sm mb-2 text-indigo-900">📥 Importing products</h2>
        <p class="text-xs text-indigo-800 mb-3">Your API import token (save this somewhere safe):</p>
        <div class="flex gap-2">
            <code class="flex-1 bg-white border border-indigo-200 rounded p-2 text-xs font-mono break-all select-all" id="token">{{ $import_token ?: '(not set — anyone can import)' }}</code>
            <button onclick="navigator.clipboard.writeText(document.getElementById('token').textContent); this.textContent='Copied!'; setTimeout(() => this.textContent='Copy', 2000)" class="px-3 py-1 bg-indigo-600 text-white rounded text-xs whitespace-nowrap">Copy</button>
        </div>
        <div class="mt-3 text-xs text-indigo-800 space-y-1">
            <div><strong>curl:</strong></div>
            <code class="block bg-white border border-indigo-200 rounded p-2 text-[11px] font-mono break-all">curl -X POST {{ url('/api/import') }} -H "Content-Type: application/json" -H "Authorization: Bearer {{ $import_token }}" -d @data.json</code>
        </div>
    </div>

    @if($admin)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 text-sm mb-6">
        <strong>👤 Signed in as:</strong> {{ $admin->name }} ({{ $admin->email }})
        <br>
        <a href="{{ route('admin.products.index') }}" class="text-amber-900 underline">→ Go to admin panel</a>
    </div>
    @endif

    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-6 text-xs text-slate-600">
        <h2 class="font-semibold text-sm text-slate-900 mb-2">🛠️ cPanel management tips</h2>
        <ul class="space-y-1 list-disc list-inside">
            <li>Need to fix permissions or clear cache? Visit <a href="{{ route('installer.tools') }}" class="text-indigo-600">System tools</a></li>
            <li>To re-run the installer: visit <a href="{{ route('installer.tools') }}" class="text-indigo-600">System tools → Reset installation</a></li>
            <li>PHP errors are logged in <code class="text-[10px]">storage/logs/laravel.log</code></li>
            <li>For cron jobs (scheduled tasks): use cPanel → Cron Jobs</li>
        </ul>
    </div>

    <div class="text-center">
        <a href="{{ route('admin.products.index') }}" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
            Go to admin panel →
        </a>
    </div>
@endsection
