@extends('layouts.app')

@section('title', request('q') ? 'Search: ' . request('q') : 'All Products')

@section('content')
    <h1 class="text-2xl font-bold mb-2">
        @if(request('q'))
            Search results for "{{ request('q') }}"
        @elseif(request('platform'))
            {{ request('platform') }} games
        @else
            All products
        @endif
    </h1>
    <p class="text-sm text-slate-500 mb-6">{{ $products->total() }} products</p>

    <!-- Filters -->
    @if($platforms->count() > 0)
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('products.index') }}" class="px-3 py-1.5 rounded-lg text-sm {{ !request('platform') && !request('category') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-700' }}">All</a>
        @foreach($platforms as $p)
        <a href="{{ route('products.index', array_merge(request()->all(), ['platform' => $p])) }}" class="px-3 py-1.5 rounded-lg text-sm {{ request('platform') == $p ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-700' }}">{{ $p }}</a>
        @endforeach
    </div>
    @endif

    @if($products->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($products as $product)
        @php $best = $product->bestOffer; $img = $product->display_image; @endphp
        <a href="{{ route('products.show', $product->id) }}" class="bg-white border border-slate-200 rounded-xl p-4 hover:shadow-lg transition-shadow">
            <div class="flex gap-3">
                @if($img)
                <img src="{{ $img }}" alt="{{ $product->name }}" class="w-20 h-20 object-cover rounded-lg flex-shrink-0" onerror="this.style.display='none'">
                @else
                <div class="w-20 h-20 bg-gradient-to-br from-slate-100 to-slate-200 rounded-lg flex-shrink-0 flex items-center justify-center text-slate-400">
                    <i class="fas fa-cube text-2xl"></i>
                </div>
                @endif
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-slate-900 line-clamp-2 mb-1">{{ $product->name }}</h3>
                    <div class="text-xs text-slate-500 mb-2">{{ $product->platform ?? '' }} · {{ $product->category ?? '' }}</div>
                    @if($best)
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-bold text-emerald-600">{{ number_format($best->price, 2) }} {{ $best->currency }}</span>
                        <span class="text-xs text-slate-500">{{ $product->offer_count }} offers</span>
                    </div>
                    @else
                    <span class="text-sm text-slate-400">No offers</span>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $products->links() }}
    </div>
    @else
    <div class="bg-white border border-slate-200 rounded-xl p-12 text-center">
        <i class="fas fa-search text-4xl text-slate-300 mb-4"></i>
        <h3 class="text-lg font-semibold text-slate-700">No products found</h3>
        <p class="text-slate-500">Try a different search or import data first.</p>
    </div>
    @endif
@endsection
