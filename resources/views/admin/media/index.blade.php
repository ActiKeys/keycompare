@extends('layouts.admin')

@section('title', 'Media Library')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Media Library</h1>
    <a href="{{ route('admin.media.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
        <i class="fas fa-upload mr-1"></i> Upload
    </a>
</div>

<!-- Stats -->
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-slate-200 rounded-xl p-4">
        <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
        <div class="text-sm text-slate-500">Total files</div>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4">
        <div class="text-2xl font-bold text-blue-600">{{ $stats['images'] }}</div>
        <div class="text-sm text-slate-500">Images</div>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4">
        <div class="text-2xl font-bold text-emerald-600">{{ $stats['featured'] }}</div>
        <div class="text-sm text-slate-500">Featured</div>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4">
        <div class="text-2xl font-bold">{{ round($stats['total_size'] / 1048576, 2) }} MB</div>
        <div class="text-sm text-slate-500">Storage</div>
    </div>
</div>

<!-- Filters -->
<form method="get" class="bg-white border border-slate-200 rounded-xl p-4 mb-6 flex flex-wrap items-end gap-3">
    <div>
        <label class="block text-xs text-slate-500 mb-1">Search</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="alt, title, filename..." class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Type</label>
        <select name="type" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
            <option value="">All</option>
            <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Images</option>
            <option value="file" {{ request('type') === 'file' ? 'selected' : '' }}>Other files</option>
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Model</label>
        <select name="model" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
            <option value="">All</option>
            <option value="App\Models\Product" {{ request('model') === 'App\Models\Product' ? 'selected' : '' }}>Products</option>
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm">Filter</button>
    <a href="{{ route('admin.media.index') }}" class="px-4 py-2 text-slate-600 text-sm">Clear</a>
</form>

@if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg p-3 mb-4 text-sm">
        {{ session('success') }}
    </div>
@endif

<!-- Grid -->
@if($media->count() > 0)
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
    @foreach($media as $item)
    <a href="{{ route('admin.media.show', $item) }}" class="bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-lg transition-shadow">
        <div class="aspect-square bg-slate-100 overflow-hidden relative">
            @if($item->is_image)
                <img src="{{ $item->url }}" alt="{{ $item->alt_text }}" class="w-full h-full object-cover" loading="lazy">
            @else
                <div class="w-full h-full flex items-center justify-center text-slate-400">
                    <i class="fas fa-file text-3xl"></i>
                </div>
            @endif
            @if($item->is_featured)
                <span class="absolute top-1 right-1 px-1.5 py-0.5 bg-amber-500 text-white text-[10px] font-bold rounded">FEATURED</span>
            @endif
        </div>
        <div class="p-3">
            <div class="text-xs font-medium text-slate-900 truncate" title="{{ $item->alt_text ?? $item->original_name }}">
                {{ $item->alt_text ?? $item->original_name ?? 'Untitled' }}
            </div>
            <div class="text-[10px] text-slate-500 mt-0.5 flex items-center justify-between">
                <span>{{ $item->human_size }}</span>
                <span class="truncate ml-1">{{ class_basename($item->mediable_type) }} #{{ $item->mediable_id }}</span>
            </div>
        </div>
    </a>
    @endforeach
</div>

<div class="mt-6">{{ $media->links() }}</div>
@else
<div class="bg-white border border-slate-200 rounded-xl p-12 text-center text-slate-500">
    <i class="fas fa-images text-4xl mb-3 text-slate-300"></i>
    <p>No media found. Import a product JSON or upload a file.</p>
</div>
@endif
@endsection
