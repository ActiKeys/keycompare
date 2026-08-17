@extends('installer.layout')

@section('content')
    <h1 class="text-2xl font-bold mb-2">Database configuration</h1>
    <p class="text-slate-600 mb-6">Enter your MySQL database credentials. You can find these in cPanel → MySQL Databases.</p>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4 text-sm">
        <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="post" action="{{ route('installer.save_database') }}" id="dbForm" class="space-y-4">
        @csrf
        <div class="grid grid-cols-3 gap-3">
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Host</label>
                <input type="text" name="host" value="{{ old('host', $defaults['host']) }}" required
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Port</label>
                <input type="number" name="port" value="{{ old('port', $defaults['port']) }}" required
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Database name</label>
            <input type="text" name="database" value="{{ old('database', $defaults['database']) }}" required
                   class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                   placeholder="e.g. user_keycompare">
            <div class="text-xs text-slate-500 mt-1">In cPanel: create the database first under "MySQL Databases"</div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Username</label>
                <input type="text" name="username" value="{{ old('username', $defaults['username']) }}" required
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                       placeholder="e.g. user_admin">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" value="{{ old('password', $defaults['password']) }}"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <button type="button" id="testBtn" class="px-4 py-2 border border-slate-200 rounded-lg text-sm hover:bg-slate-50">
                🔌 Test connection
            </button>
            <div class="flex gap-2">
                <a href="{{ route('installer.welcome') }}" class="px-4 py-2 text-slate-600 hover:text-slate-800">← Back</a>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">
                    Continue →
                </button>
            </div>
        </div>
        <div id="testResult" class="hidden"></div>
    </form>

    <script>
    document.getElementById('testBtn').addEventListener('click', async function() {
        const form = document.getElementById('dbForm');
        const data = Object.fromEntries(new FormData(form));
        const resultEl = document.getElementById('testResult');
        resultEl.className = 'p-3 rounded-lg text-sm';
        resultEl.textContent = 'Testing...';
        resultEl.classList.remove('hidden');
        try {
            const r = await fetch('{{ route("installer.test_database") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify(data),
            });
            const j = await r.json();
            if (j.ok) {
                resultEl.className = 'p-3 rounded-lg text-sm bg-emerald-50 text-emerald-800';
                resultEl.textContent = '✓ ' + j.message;
            } else {
                resultEl.className = 'p-3 rounded-lg text-sm bg-red-50 text-red-800';
                resultEl.textContent = '✗ ' + j.message;
            }
        } catch (e) {
            resultEl.className = 'p-3 rounded-lg text-sm bg-red-50 text-red-800';
            resultEl.textContent = '✗ Network error: ' + e.message;
        }
    });
    </script>
@endsection
