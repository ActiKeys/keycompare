@extends('layouts.admin')

@section('title', $medium->alt_text ?? $medium->original_name ?? 'Media #' . $medium->id)

@section('content')
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('admin.media.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Back to media</a>
    <form method="post" action="{{ route('admin.media.destroy', $medium) }}" onsubmit="return confirm('Delete this media? This cannot be undone.')">
        @csrf @method('delete')
        <button type="submit" class="px-3 py-1.5 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50">
            <i class="fas fa-trash mr-1"></i> Delete
        </button>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Preview -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="aspect-square bg-slate-100 flex items-center justify-center overflow-hidden">
            @if($medium->is_image)
                <img src="{{ $medium->url }}" alt="{{ $medium->alt_text }}" class="max-w-full max-h-full object-contain">
            @else
                <i class="fas fa-file text-6xl text-slate-400"></i>
            @endif
        </div>
        <div class="p-4 text-sm text-slate-500 border-t border-slate-200">
            <div class="flex justify-between mb-1">
                <span>URL</span>
                <a href="{{ $medium->url }}" target="_blank" class="text-indigo-600 hover:underline truncate ml-2">{{ $medium->url }}</a>
            </div>
            <div class="flex justify-between mb-1"><span>MIME</span><span>{{ $medium->mime_type }}</span></div>
            <div class="flex justify-between mb-1"><span>Size</span><span>{{ $medium->human_size }}</span></div>
            @if($medium->width)
            <div class="flex justify-between mb-1"><span>Dimensions</span><span>{{ $medium->width }} × {{ $medium->height }}</span></div>
            @endif
            <div class="flex justify-between mb-1"><span>Collection</span><span>{{ $medium->collection }}</span></div>
            <div class="flex justify-between mb-1"><span>Featured</span><span>{{ $medium->is_featured ? 'Yes' : 'No' }}</span></div>
            <div class="flex justify-between mb-1"><span>Attached to</span><span>{{ class_basename($medium->mediable_type) }} #{{ $medium->mediable_id }}</span></div>
            @if($medium->source_url)
            <div class="flex justify-between mb-1">
                <span>Source</span>
                <a href="{{ $medium->source_url }}" target="_blank" class="text-indigo-600 hover:underline truncate ml-2">{{ $medium->source_url }}</a>
            </div>
            @endif
        </div>
    </div>

    <!-- Edit form -->
    <form method="post" action="{{ route('admin.media.update', $medium) }}" class="bg-white border border-slate-200 rounded-xl p-6 space-y-4 h-fit">
        @csrf @method('put')

        <h2 class="font-semibold text-lg">Edit metadata</h2>

        <div>
            <label class="block text-sm font-medium mb-1">Alt text <span class="text-slate-400">(for accessibility & SEO)</span></label>
            <input type="text" name="alt_text" value="{{ old('alt_text', $medium->alt_text) }}" maxlength="255"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="Describe what's in the image">
            <div class="text-xs text-slate-500 mt-1">Tip: write a short description for screen readers and SEO</div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Title <span class="text-slate-400">(tooltip)</span></label>
            <input type="text" name="title" value="{{ old('title', $medium->title) }}" maxlength="255"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Caption</label>
            <textarea name="caption" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">{{ old('caption', $medium->caption) }}</textarea>
        </div>

        <div>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $medium->is_featured) ? 'checked' : '' }} class="rounded">
                <span class="text-sm">Mark as <strong>featured image</strong> for this product</span>
            </label>
            <div class="text-xs text-slate-500 mt-1">Only one image can be featured per product</div>
        </div>

        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>

        @if(session('success'))
            <div class="text-sm text-emerald-600">{{ session('success') }}</div>
        @endif
    </form>
</div>
@endsection
