@extends('layouts.admin')

@section('title', 'Upload Media')

@section('content')
<a href="{{ route('admin.media.index') }}" class="text-sm text-slate-500 hover:text-slate-700 mb-4 inline-block">← Back to media</a>

<div class="bg-white border border-slate-200 rounded-xl p-6 max-w-2xl">
    <h1 class="text-xl font-bold mb-4">Upload media</h1>

    <form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">File</label>
            <input type="file" name="file" required accept="image/*"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded file:border-0 file:bg-slate-100 file:text-slate-700">
            <div class="text-xs text-slate-500 mt-1">Max 10MB · JPG, PNG, GIF, WebP, SVG, PDF</div>
            @error('file')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Attach to</label>
                <select name="mediable_type" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    <option value="App\Models\Product">Product</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">ID</label>
                <input type="number" name="mediable_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="e.g. 1">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Collection</label>
            <input type="text" name="collection" value="featured" required maxlength="64"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="featured, gallery, icon...">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Alt text</label>
            <input type="text" name="alt_text" maxlength="255"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="Describe the image for accessibility">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Title <span class="text-slate-400">(optional)</span></label>
            <input type="text" name="title" maxlength="255" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Caption <span class="text-slate-400">(optional)</span></label>
            <textarea name="caption" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"></textarea>
        </div>

        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_featured" value="1" class="rounded">
            <span class="text-sm">Set as <strong>featured</strong> image</span>
        </label>

        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i class="fas fa-upload mr-1"></i> Upload
        </button>
    </form>

    @if($errors->any())
        <div class="mt-4 bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm">
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
</div>
@endsection
