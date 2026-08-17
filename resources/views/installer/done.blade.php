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
            <a href="{{ url('/admin') }}" target="_blank" class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-lg hover:border-indigo-300">
                <div>
                    <div class="font-medium text-sm">⚙️ Admin panel</div>
                    <div class="text-xs text-slate-500">Manage products, media, and imports</div>
                </div>
                <span class="text-indigo-600">→</span>
            </a>
            <a href="{{ url('/admin/media') }}" target="_blank" class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-lg hover:border-indigo-300">
                <div>
                    <div class="font-medium text-sm">📸 Media library</div>
                    <div class="text-xs text-slate-500">Upload and manage images</div>
                </div>
                <span class="text-indigo-600">→</span>
            </a>
        </div>
    </div>

    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6">
        <h2 class="font-semibold text-sm mb-2 text-indigo-900">📥 Importing products</h2>
        <p class="text-xs text-indigo-800 mb-3">Your API import token (save this somewhere safe):</p>
        <code class="block bg-white border border-indigo-200 rounded p-2 text-xs font-mono break-all">{{ $import_token ?: '(not set — anyone can import)' }}</code>
        <div class="mt-3 text-xs text-indigo-800 space-y-1">
            <div><strong>Python:</strong> <code>python /path/to/examples/push_products.py data.json --url {{ url('/api/import') }} --token {{ $import_token }}</code></div>
            <div><strong>curl:</strong> <code>curl -X POST {{ url('/api/import') }} -H "Content-Type: application/json" -H "Authorization: Bearer {{ $import_token }}" -d @data.json</code></div>
        </div>
    </div>

    @if($admin)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 text-sm mb-6">
        <strong>👤 Signed in as:</strong> {{ $admin->name }} ({{ $admin->email }})<br>
        <a href="{{ url('/admin') }}" class="text-amber-900 underline">Click here to go to the admin panel</a>
    </div>
    @endif

    <div class="text-center">
        <a href="{{ url('/') }}" class="inline-block px-6 py-2 bg-slate-900 text-white rounded-lg text-sm font-medium hover:bg-slate-800">
            Visit your site →
        </a>
    </div>
@endsection
