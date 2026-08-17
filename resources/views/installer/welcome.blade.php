@extends('installer.layout')

@section('content')
    <h1 class="text-2xl font-bold mb-2">Welcome to KeyCompare</h1>
    <p class="text-slate-600 mb-6">Let's get your price comparison site set up. This wizard will check your server, configure the database, and create your admin account.</p>

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
            </div>
        </div>
        @endforeach
    </div>

    @if($allPassed)
    <div class="flex justify-end pt-4 border-t border-slate-100">
        <a href="{{ route('installer.database') }}" class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">
            Continue →
        </a>
    </div>
    @else
    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 text-sm">
        <strong>Please fix the failed requirements above before continuing.</strong> Your hosting provider should be able to enable missing PHP extensions.
    </div>
    @endif
@endsection
