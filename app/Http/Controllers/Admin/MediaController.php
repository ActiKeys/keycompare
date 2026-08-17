<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Product;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function __construct(private MediaService $media) {}

    /**
     * List all media (with filters).
     */
    public function index(Request $request)
    {
        $query = Media::query()->with('mediable');

        if ($q = $request->get('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('alt_text', 'like', "%{$q}%")
                   ->orWhere('title', 'like', "%{$q}%")
                   ->orWhere('caption', 'like', "%{$q}%")
                   ->orWhere('original_name', 'like', "%{$q}%");
            });
        }

        if ($type = $request->get('type')) {
            if ($type === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            } elseif ($type === 'file') {
                $query->where('mime_type', 'not like', 'image/%');
            }
        }

        if ($model = $request->get('model')) {
            $query->where('mediable_type', $model);
        }

        $media = $query->orderBy('id', 'desc')->paginate(30)->withQueryString();

        $stats = [
            'total' => Media::count(),
            'images' => Media::where('mime_type', 'like', 'image/%')->count(),
            'featured' => Media::where('is_featured', true)->count(),
            'total_size' => Media::sum('size'),
        ];

        return view('admin.media.index', compact('media', 'stats'));
    }

    /**
     * Show single media for editing.
     */
    public function show(Media $medium)
    {
        $medium->load('mediable');
        return view('admin.media.show', compact('medium'));
    }

    /**
     * Update alt text / title / caption / featured status.
     */
    public function update(Request $request, Media $medium)
    {
        $data = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
            'is_featured' => 'sometimes|boolean',
        ]);

        $data['is_featured'] = $request->boolean('is_featured');

        if ($data['is_featured']) {
            $this->media->setFeatured($medium);
        } else {
            $medium->update($data);
        }

        return redirect()->back()->with('success', 'Media updated');
    }

    /**
     * Delete a media item.
     */
    public function destroy(Media $medium)
    {
        $this->media->delete($medium);
        return redirect()->route('admin.media.index')->with('success', 'Media deleted');
    }

    /**
     * Manual upload form (any model).
     */
    public function create()
    {
        $products = Product::orderBy('name')->limit(200)->get(['id', 'name']);
        return view('admin.media.create', compact('products'));
    }

    /**
     * Handle manual upload.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,avif,svg,pdf',
            'mediable_type' => 'required|string',
            'mediable_id' => 'required|integer',
            'collection' => 'required|string|max:64',
            'alt_text' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
            'is_featured' => 'sometimes|boolean',
        ]);

        $modelClass = $data['mediable_type'];
        if (!class_exists($modelClass)) {
            return back()->withErrors(['mediable_type' => 'Invalid model type']);
        }
        $model = $modelClass::find($data['mediable_id']);
        if (!$model) {
            return back()->withErrors(['mediable_id' => 'Model not found']);
        }

        $media = $this->media->upload(
            model: $model,
            collection: $data['collection'],
            source: $request->file('file'),
            altText: $data['alt_text'] ?? null,
            title: $data['title'] ?? null,
            caption: $data['caption'] ?? null,
            isFeatured: $request->boolean('is_featured'),
        );

        return redirect()->route('admin.media.show', $media)->with('success', 'Media uploaded');
    }
}
