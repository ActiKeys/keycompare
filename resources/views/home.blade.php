@extends('layouts.app')

@section('title', 'KeyCompare — Find the cheapest key across every store')

@section('content')
    <!-- Hero -->
    <div class="bg-gradient-to-br from-slate-900 to-indigo-900 text-white rounded-2xl p-8 mb-8">
        <div class="text-center">
            <span class="inline-block px-3 py-1 bg-emerald-500/20 border border-emerald-400/30 rounded-full text-xs text-emerald-300 mb-4">
                <i class="fas fa-circle text-emerald-400 text-[8px] mr-1"></i> Live pricing
            </span>
            <h1 class="text-4xl md:text-5xl font-bold mb-3">
                Find the <span class="text-indigo-300">cheapest key</span><br>across every store
            </h1>
            <p class="text-slate-300 max-w-xl mx-auto">
                Real-time price comparison for game keys, software, and subscriptions. Never overpay.
            </p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-white border border-slate-200 rounded-xl p-5 text-center">
            <div class="text-3xl font-bold text-slate-900">{{ number_format($stats['products']) }}</div>
            <div class="text-sm text-slate-500 mt-1">Products tracked</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 text-center">
            <div class="text-3xl font-bold text-emerald-600">{{ number_format($stats['offers']) }}</div>
            <div class="text-sm text-slate-500 mt-1">Active offers</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 text-center">
            <div class="text-3xl font-bold text-slate-900">{{ number_format($stats['stores']) }}</div>
            <div class="text-sm text-slate-500 mt-1">Stores</div>
        </div>
    </div>

    <!-- Platforms -->
    @if($platforms->count() > 0)
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-lg text-sm {{ !request('platform') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:border-indigo-300' }}">All</a>
        @foreach($platforms as $p)
        <a href="{{ route('products.index', ['platform' => $p]) }}" class="px-3 py-1.5 rounded-lg text-sm {{ request('platform') == $p ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:border-indigo-300' }}">{{ $p }}</a>
        @endforeach
    </div>
    @endif

    <!-- Featured products -->
    <h2 class="text-2xl font-bold mb-4">Latest products</h2>
    @if($featured->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($featured as $product)
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
                    <div class="text-xs text-slate-500 mb-2">{{ $product->platform ?? 'Various' }} · {{ $product->category ?? '' }}</div>
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
    @else
    <div class="bg-white border border-slate-200 rounded-xl p-12 text-center">
        <i class="fas fa-inbox text-4xl text-slate-300 mb-4"></i>
        <h3 class="text-lg font-semibold text-slate-700 mb-2">No products yet</h3>
        <p class="text-slate-500 mb-4">Import products via the API or admin panel to get started.</p>
        <a href="/admin" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Go to admin</a>
    </div>
    @endif
@endsection
