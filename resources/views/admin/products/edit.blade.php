@extends('layouts.admin')

@section('title', 'Edit: ' . $product->name)

@section('content')
<a href="{{ route('admin.products.index') }}" class="text-sm text-slate-500 hover:text-slate-700 mb-4 inline-block">← Back to products</a>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Product info -->
    <form method="post" action="{{ route('admin.products.update', $product) }}" class="bg-white border border-slate-200 rounded-xl p-6 space-y-4">
        @csrf @method('put')
        <h1 class="text-lg font-bold">Edit product</h1>
        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Platform</label>
            <input type="text" name="platform" value="{{ old('platform', $product->platform) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Category</label>
            <input type="text" name="category" value="{{ old('category', $product->category) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="4" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">{{ old('description', $product->description) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">External image URL (fallback)</label>
            <input type="url" name="image_link" value="{{ old('image_link', $product->image_link) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>
    </form>

    <!-- Featured image + media picker -->
    <div class="space-y-4">
        <!-- Current featured -->
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h2 class="font-bold mb-3">Featured image</h2>
            @php $featured = $product->featuredImage()->first(); @endphp
            @if($featured)
                <img src="{{ $featured->url }}" alt="{{ $featured->alt_text }}" class="w-full aspect-square object-cover rounded-lg mb-3">
                <div class="text-sm space-y-1">
                    <div><strong>Alt:</strong> {{ $featured->alt_text ?? '—' }}</div>
                    <div><strong>Size:</strong> {{ $featured->human_size }} ({{ $featured->width }}×{{ $featured->height }})</div>
                </div>
                <a href="{{ route('admin.media.show', $featured) }}" class="block mt-3 text-center px-3 py-2 border border-slate-200 rounded-lg text-sm hover:bg-slate-50">Edit metadata</a>
            @else
                <div class="aspect-square bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 mb-3">
                    <i class="fas fa-image text-3xl"></i>
                </div>
                <p class="text-sm text-slate-500 text-center">No featured image set. Pick one from the gallery or upload a new one.</p>
            @endif
        </div>

        <!-- All media for this product -->
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h2 class="font-bold mb-3">All media ({{ $product->media->count() }})</h2>
            <div class="grid grid-cols-3 gap-2">
                @forelse($product->media as $m)
                    <a href="{{ route('admin.media.show', $m) }}" class="relative aspect-square bg-slate-100 rounded overflow-hidden group">
                        <img src="{{ $m->url }}" class="w-full h-full object-cover" alt="{{ $m->alt_text }}">
                        @if($m->is_featured)
                            <span class="absolute top-0 right-0 px-1 bg-amber-500 text-white text-[9px] font-bold">★</span>
                        @endif
                    </a>
                @empty
                    <div class="col-span-3 text-center text-sm text-slate-500 py-4">No media attached</div>
                @endforelse
            </div>
            <a href="{{ route('admin.media.create', ['mediable_type' => 'App\\Models\\Product', 'mediable_id' => $product->id]) }}" class="block mt-3 text-center px-3 py-2 border border-slate-200 rounded-lg text-sm hover:bg-slate-50">
                <i class="fas fa-upload mr-1"></i> Upload new
            </a>
        </div>
    </div>
</div>
@endsection
