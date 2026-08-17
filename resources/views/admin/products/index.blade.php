@extends('layouts.admin')

@section('title', 'Products')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Products</h1>
    <a href="{{ route('admin.media.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
        <i class="fas fa-upload mr-1"></i> Upload Media
    </a>
</div>

@if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg p-3 mb-4 text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-left text-slate-500">
            <tr>
                <th class="p-3">Image</th>
                <th class="p-3">Name</th>
                <th class="p-3">Platform</th>
                <th class="p-3">Offers</th>
                <th class="p-3">Updated</th>
                <th class="p-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($products as $p)
            <tr>
                <td class="p-3">
                    @php $img = $p->display_image; @endphp
                    @if($img)
                        <img src="{{ $img }}" class="w-12 h-12 object-cover rounded" alt="" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22><text>%F0%9F%93%A6</text></svg>'">
                    @else
                        <div class="w-12 h-12 bg-slate-100 rounded"></div>
                    @endif
                </td>
                <td class="p-3 font-medium">{{ $p->name }}</td>
                <td class="p-3 text-slate-500">{{ $p->platform ?? '—' }}</td>
                <td class="p-3 text-slate-500">{{ $p->offer_count }}</td>
                <td class="p-3 text-slate-400 text-xs">{{ $p->updated_at?->diffForHumans() }}</td>
                <td class="p-3 text-right">
                    <a href="{{ route('admin.products.edit', $p) }}" class="text-indigo-600 hover:underline text-xs">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $products->links() }}</div>
@endsection
