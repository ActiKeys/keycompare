@extends('layouts.admin')

@section('title', 'Edit: ' . $product->name)

@section('content')
<a href="{{ route('admin.products.index') }}" class="text-sm text-slate-500 hover:text-slate-700 mb-4 inline-block">← Back to products</a>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg p-3 mb-4 text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6" x-data="{ tab: 'general' }">
    <!-- Product info -->
    <div class="space-y-4">
        <div class="bg-white border border-slate-200 rounded-xl">
            <div class="flex border-b border-slate-200">
                <button @click="tab = 'general'" :class="tab === 'general' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-slate-500'" class="px-4 py-3 text-sm font-medium">General</button>
                <button @click="tab = 'media'" :class="tab === 'media' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-slate-500'" class="px-4 py-3 text-sm font-medium">Media ({{ $product->media->count() }})</button>
                <button @click="tab = 'offers'" :class="tab === 'offers' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-slate-500'" class="px-4 py-3 text-sm font-medium">Offers ({{ $product->offers->count() }})</button>
            </div>

            <div x-show="tab === 'general'" class="p-6 space-y-4">
                <form method="post" action="{{ route('admin.products.update', $product) }}" class="space-y-4">
                    @csrf @method('put')
                    <h2 class="text-lg font-bold">Edit product</h2>
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Platform</label>
                            <input type="text" name="platform" value="{{ old('platform', $product->platform) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Category</label>
                            <input type="text" name="category" value="{{ old('category', $product->category) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea name="description" rows="4" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">{{ old('description', $product->description) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">External image URL <span class="text-slate-400">(fallback if no media)</span></label>
                        <input type="url" name="image_link" value="{{ old('image_link', $product->image_link) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>
                </form>
            </div>

            <div x-show="tab === 'media'" x-cloak class="p-6 space-y-4">
                <h2 class="text-lg font-bold">Media library</h2>
                <p class="text-sm text-slate-500">Manage all images for this product. Click the star to set featured.</p>

                {{-- Image picker (featured) --}}
                <x-image-picker
                    name="featured_media_id"
                    :value="$product->featuredImage()->first()?->id"
                    collection="featured"
                    :model="'App\\\\Models\\\\Product'"
                    :modelId="$product->id"
                />

                <hr class="my-4">

                <h3 class="font-semibold text-sm">All media ({{ $product->media->count() }})</h3>
                <div class="grid grid-cols-3 gap-2">
                    @forelse($product->media as $m)
                    <div class="relative group">
                        <a href="{{ route('admin.media.show', $m) }}" target="_blank" class="block aspect-square bg-slate-100 rounded overflow-hidden">
                            <img src="{{ $m->url }}" class="w-full h-full object-cover" alt="{{ $m->alt_text }}">
                        </a>
                        <button type="button"
                                onclick="fetch('{{ route('admin.media.update', $m) }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify({ _method: 'PUT', is_featured: true }) }).then(r => r.ok ? location.reload() : alert('Failed'))"
                                class="absolute top-1 right-1 w-6 h-6 rounded text-xs {{ $m->is_featured ? 'bg-amber-500 text-white' : 'bg-white/80 text-slate-600' }} hover:bg-amber-500 hover:text-white"
                                title="Set as featured">
                            ★
                        </button>
                    </div>
                    @empty
                    <div class="col-span-3 text-center text-sm text-slate-500 py-4">No media yet — upload one above</div>
                    @endforelse
                </div>
            </div>

            <div x-show="tab === 'offers'" x-cloak class="p-6">
                <h2 class="text-lg font-bold mb-3">Offers ({{ $product->offers->count() }})</h2>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($product->offers()->orderBy('price')->limit(50)->get() as $offer)
                    <div class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium truncate">{{ $offer->store->name ?? '—' }}</div>
                            <a href="{{ $offer->link }}" target="_blank" class="text-xs text-slate-500 truncate block">{{ $offer->link }}</a>
                        </div>
                        <div class="text-sm font-bold">{{ number_format($offer->price, 2) }} {{ $offer->currency }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Right: preview -->
    <div class="space-y-4">
        <div class="bg-white border border-slate-200 rounded-xl p-6 sticky top-4">
            <h3 class="font-bold mb-3">Preview</h3>
            @php $featured = $product->featuredImage()->first(); @endphp
            @if($featured)
                <img src="{{ $featured->url }}" alt="{{ $featured->alt_text }}" class="w-full aspect-square object-cover rounded-lg mb-3">
                <div class="text-sm">
                    <div><strong>Alt:</strong> {{ $featured->alt_text ?? '—' }}</div>
                    <div><strong>Size:</strong> {{ $featured->human_size }}</div>
                </div>
            @elseif($product->image_link)
                <img src="{{ $product->image_link }}" class="w-full aspect-square object-cover rounded-lg mb-3">
                <div class="text-xs text-slate-500">External image (not yet downloaded)</div>
            @else
                <div class="aspect-square bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 mb-3">
                    <i class="text-4xl">📦</i>
                </div>
            @endif
            <a href="{{ route('products.show', $product->id) }}" target="_blank" class="block mt-3 text-center text-sm text-indigo-600 hover:underline">
                View on public site →
            </a>
        </div>
    </div>
</div>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>[x-cloak]{display:none!important}</style>
@endsection
