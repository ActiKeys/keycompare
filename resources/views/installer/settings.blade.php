@extends('installer.layout')

@section('content')
    <h1 class="text-2xl font-bold mb-2">Site settings</h1>
    <p class="text-slate-600 mb-6">Last step! Configure your site name and API access.</p>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4 text-sm">
        <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="post" action="{{ route('installer.save_settings') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Site name</label>
            <input type="text" name="app_name" value="{{ old('app_name', $defaults['app_name']) }}" required
                   class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                   placeholder="KeyCompare">
            <div class="text-xs text-slate-500 mt-1">Shown in the header and browser title</div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">API import token (optional)</label>
            <div class="flex gap-2">
                <input type="text" name="import_token" id="importToken"
                       class="flex-1 px-3 py-2 border border-slate-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500"
                       placeholder="Leave blank to auto-generate">
                <button type="button" onclick="document.getElementById('importToken').value = Array.from(crypto.getRandomValues(new Uint8Array(32))).map(b=>b.toString(16).padStart(2,'0')).join('')" class="px-3 py-2 border border-slate-200 rounded-lg text-sm hover:bg-slate-50">
                    🎲 Generate
                </button>
            </div>
            <div class="text-xs text-slate-500 mt-1">Required to use the JSON import API. You can change this later in the admin panel.</div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <a href="{{ route('installer.admin') }}" class="px-4 py-2 text-slate-600 hover:text-slate-800">← Back</a>
            <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700">
                Finish installation ✓
            </button>
        </div>
    </form>
@endsection
