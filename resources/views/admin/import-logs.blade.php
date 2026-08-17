@extends('layouts.admin')

@section('title', 'Import Logs')

@section('content')
<h1 class="text-2xl font-bold mb-6">Import logs</h1>

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-left text-slate-500">
            <tr>
                <th class="p-3">ID</th>
                <th class="p-3">Source</th>
                <th class="p-3">Status</th>
                <th class="p-3">Products</th>
                <th class="p-3">Duration</th>
                <th class="p-3">When</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($logs as $log)
            <tr>
                <td class="p-3 text-slate-500">#{{ $log->id }}</td>
                <td class="p-3">{{ $log->source }}</td>
                <td class="p-3">
                    <span class="px-2 py-0.5 text-xs rounded
                        {{ $log->status === 'success' ? 'bg-emerald-100 text-emerald-700' :
                           ($log->status === 'partial' ? 'bg-amber-100 text-amber-700' :
                           'bg-red-100 text-red-700') }}">
                        {{ $log->status }}
                    </span>
                </td>
                <td class="p-3 text-slate-600">
                    {{ $log->products_count }} (c:{{ $log->products_created }} u:{{ $log->products_updated }})
                    @if($log->meta && isset($log->meta['media_attached']))
                        <br><span class="text-xs text-slate-400">{{ $log->meta['media_attached'] }} media attached, {{ $log->meta['media_failed'] }} failed</span>
                    @endif
                </td>
                <td class="p-3 text-slate-500">{{ $log->duration_ms }}ms</td>
                <td class="p-3 text-slate-400 text-xs">{{ $log->created_at?->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-8 text-center text-slate-500">No imports yet</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
