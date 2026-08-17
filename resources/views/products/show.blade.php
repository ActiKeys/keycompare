@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <a href="{{ route('products.index') }}" class="text-sm text-slate-500 hover:text-slate-700 mb-4 inline-block">
        <i class="fas fa-arrow-left"></i> Back to products
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: product info -->
        <div class="lg:col-span-1">
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden sticky top-20">
                @php $img = $product->display_image; @endphp
                @if($img)
                <img src="{{ $img }}" class="w-full aspect-square object-cover" alt="{{ $product->name }}" onerror="this.outerHTML='<div class=&quot;w-full aspect-square bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-400&quot;><i class=&quot;fas fa-cube text-6xl&quot;></i></div>'">
                @else
                <div class="w-full aspect-square bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-400">
                    <i class="fas fa-cube text-6xl"></i>
                </div>
                @endif
                <div class="p-5">
                    <h1 class="text-xl font-bold mb-2">{{ $product->name }}</h1>
                    <div class="space-y-2 text-sm mb-4">
                        @if($product->platform)
                        <div class="flex justify-between"><span class="text-slate-500">Platform</span><span class="font-medium">{{ $product->platform }}</span></div>
                        @endif
                        @if($product->category)
                        <div class="flex justify-between"><span class="text-slate-500">Category</span><span class="font-medium">{{ $product->category }}</span></div>
                        @endif
                        <div class="flex justify-between"><span class="text-slate-500">Offers</span><span class="font-medium">{{ $offers->count() }}</span></div>
                    </div>
                    @if($product->tags && count($product->tags) > 0)
                    <div class="flex flex-wrap gap-1 mb-4">
                        @foreach($product->tags as $tag)
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-xs rounded">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                    @if($product->description)
                    <details class="text-sm text-slate-600">
                        <summary class="cursor-pointer font-medium mb-1">Description</summary>
                        <p class="text-xs leading-relaxed whitespace-pre-line">{{ \Illuminate\Support\Str::limit($product->description, 600) }}</p>
                    </details>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: offers -->
        <div class="lg:col-span-2">
            @if($bestOffer)
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-xl p-6 mb-6">
                <div class="text-sm font-medium opacity-80 mb-1">BEST DEAL</div>
                <div class="text-4xl font-bold mb-1">{{ number_format($bestOffer->price, 2) }} {{ $bestOffer->currency }}</div>
                <div class="text-sm opacity-80">at {{ $bestOffer->store->name ?? 'Unknown store' }}</div>
                <a href="{{ $bestOffer->link }}" target="_blank" rel="nofollow noopener" class="mt-4 inline-block px-5 py-2.5 bg-white text-emerald-700 rounded-lg font-medium hover:bg-emerald-50">
                    Get this key <i class="fas fa-external-link-alt ml-1 text-xs"></i>
                </a>
            </div>
            @endif

            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="font-semibold">Price comparison</h2>
                    <span class="text-xs text-slate-500">{{ $offers->count() }} offers · sorted by price</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($offers as $offer)
                    <a href="{{ $offer->link }}" target="_blank" rel="nofollow noopener"
                       class="flex items-center gap-4 px-5 py-3 hover:bg-slate-50 transition-colors">
                        <div class="w-10 h-10 rounded bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-500 text-xs font-bold uppercase flex-shrink-0">
                            {{ substr($offer->store->name ?? '?', 0, 2) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-slate-900 truncate">{{ $offer->store->name ?? 'Unknown' }}</div>
                            <div class="text-xs text-slate-500">
                                @if($offer->region){{ $offer->region }} · @endif
                                @if($offer->in_stock)<span class="text-emerald-600">In stock</span>@else<span class="text-red-500">Out of stock</span>@endif
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-lg font-bold {{ $offer->id === $bestOffer?->id ? 'text-emerald-600' : 'text-slate-900' }}">
                                {{ number_format($offer->price, 2) }} {{ $offer->currency }}
                            </div>
                            @if($offer->id === $bestOffer?->id)
                            <div class="text-xs text-emerald-600 font-medium">Best</div>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
