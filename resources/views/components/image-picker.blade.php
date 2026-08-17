@props([
    'name' => 'media_id',
    'value' => null,
    'collection' => 'featured',
    'model' => null,        // model class for upload
    'modelId' => null,      // model id for upload
    'multiple' => false,
])

@php
    $currentMedia = $value ? \App\Models\Media::find($value) : null;
@endphp

<div x-data="imagePicker({
    multiple: @json($multiple),
    collection: @js($collection),
    model: @js($model),
    modelId: @js($modelId),
})" x-init="init()" class="space-y-3">
    <input type="hidden" :name="'{{ $name }}'" :value="selected">

    {{-- Current selection preview --}}
    <div x-show="current" class="relative aspect-video bg-slate-100 rounded-lg overflow-hidden">
        <img :src="currentUrl" :alt="currentAlt" class="w-full h-full object-contain">
        <div class="absolute top-2 right-2 flex gap-1">
            <button type="button" @click="openPicker" class="px-2 py-1 bg-white/90 rounded text-xs hover:bg-white">Change</button>
            <button type="button" @click="clear" class="px-2 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600">Remove</button>
        </div>
    </div>
    <div x-show="!current">
        <button type="button" @click="openPicker" class="w-full aspect-video border-2 border-dashed border-slate-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50/30 transition-colors flex flex-col items-center justify-center text-slate-500">
            <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="text-sm font-medium">Choose from Media Library</span>
            <span class="text-xs text-slate-400">or upload new</span>
        </button>
    </div>

    {{-- Picker modal --}}
    <div x-show="pickerOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" @keydown.escape.window="pickerOpen = false">
        <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] flex flex-col" @click.outside="pickerOpen = false">
            <div class="flex items-center justify-between p-4 border-b border-slate-200">
                <h2 class="font-bold text-lg">Choose image</h2>
                <button type="button" @click="pickerOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto p-4">
                {{-- Upload form --}}
                <div class="mb-4 p-4 bg-slate-50 rounded-lg">
                    <h3 class="font-medium text-sm mb-2">Upload new</h3>
                    <div class="flex gap-2">
                        <input type="file" @change="upload($event)" accept="image/*" :disabled="uploading"
                               class="flex-1 text-sm">
                        <span x-show="uploading" class="text-xs text-slate-500 self-center">Uploading...</span>
                    </div>
                    <div x-show="uploadError" class="text-xs text-red-600 mt-1" x-text="uploadError"></div>
                </div>

                {{-- Gallery --}}
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-medium text-sm">Library</h3>
                    <div class="flex items-center gap-2">
                        <input type="text" x-model.debounce.300ms="search" placeholder="Search..."
                               class="text-sm border border-slate-200 rounded px-2 py-1">
                        <button type="button" @click="loadMedia" class="text-xs text-indigo-600">↻</button>
                    </div>
                </div>

                <div x-show="loading" class="text-center text-slate-500 py-8">Loading...</div>
                <div x-show="!loading && media.length === 0" class="text-center text-slate-500 py-8">
                    No images yet. Upload one above.
                </div>

                <div class="grid grid-cols-3 md:grid-cols-5 gap-2" x-show="media.length > 0">
                    <template x-for="item in media" :key="item.id">
                        <button type="button" @click="select(item)"
                                :class="selected == item.id ? 'ring-2 ring-indigo-600' : ''"
                                class="relative aspect-square bg-slate-100 rounded overflow-hidden group hover:opacity-90">
                            <img :src="item.url" :alt="item.alt_text" class="w-full h-full object-cover" loading="lazy">
                            <div class="absolute inset-x-0 bottom-0 bg-black/60 text-white text-[10px] p-1 truncate" x-text="item.alt_text || item.original_name"></div>
                            <div x-show="item.is_featured" class="absolute top-1 right-1 px-1 bg-amber-500 text-white text-[9px] font-bold rounded">★</div>
                        </button>
                    </template>
                </div>
            </div>

            <div class="p-4 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" @click="pickerOpen = false" class="px-4 py-2 text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="button" @click="confirm" :disabled="!selected" class="px-6 py-2 bg-indigo-600 text-white rounded-lg disabled:opacity-50">Select</button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
function imagePicker(opts) {
    return {
        pickerOpen: false,
        media: [],
        selected: opts.multiple ? [] : (@js($value) ?? null),
        loading: false,
        uploading: false,
        uploadError: '',
        search: '',
        collection: opts.collection,
        model: opts.model,
        modelId: opts.modelId,
        get current() { return this.media.find(m => m.id == this.selected) ?? null; },
        get currentUrl() { return this.current?.url ?? ''; },
        get currentAlt() { return this.current?.alt_text ?? ''; },
        async init() {
            // Set initial selected from prop
            const initial = @js($currentMedia);
            if (initial && !this.selected) {
                this.selected = initial.id;
                this.media = [initial];
            }
            await this.loadMedia();
        },
        async loadMedia() {
            this.loading = true;
            try {
                const r = await fetch(`{{ url('/api/media') }}?type=image&q=${encodeURIComponent(this.search)}`);
                const j = await r.json();
                this.media = j.data || [];
            } catch (e) {
                console.error('loadMedia failed', e);
            } finally {
                this.loading = false;
            }
        },
        openPicker() {
            this.pickerOpen = true;
            this.loadMedia();
        },
        select(item) {
            this.selected = opts.multiple
                ? (this.selected.includes(item.id) ? this.selected.filter(x => x !== item.id) : [...this.selected, item.id])
                : item.id;
        },
        confirm() {
            this.pickerOpen = false;
        },
        clear() {
            this.selected = opts.multiple ? [] : null;
        },
        async upload(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.uploading = true;
            this.uploadError = '';
            const formData = new FormData();
            formData.append('file', file);
            formData.append('collection', this.collection);
            if (this.model) formData.append('mediable_type', this.model);
            if (this.modelId) formData.append('mediable_id', this.modelId);
            formData.append('is_featured', '1');
            formData.append('alt_text', file.name);
            try {
                const r = await fetch('{{ url('/api/media/upload') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                if (!r.ok) throw new Error('Upload failed');
                const j = await r.json();
                this.selected = j.id;
                this.media.unshift(j);
                event.target.value = '';
            } catch (e) {
                this.uploadError = e.message;
            } finally {
                this.uploading = false;
            }
        },
    };
}
</script>
@endpush
@endonce
