<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaApiController extends Controller
{
    public function __construct(private MediaService $media) {}

    /**
     * GET /api/media
     * List media (for picker).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Media::query()->orderBy('id', 'desc');

        if ($q = $request->get('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('alt_text', 'like', "%{$q}%")
                   ->orWhere('original_name', 'like', "%{$q}%");
            });
        }

        if ($type = $request->get('type')) {
            if ($type === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            }
        }

        if ($request->get('model')) {
            $query->where('mediable_type', $request->get('model'));
        }

        $items = $query->limit(60)->get()->map(fn($m) => [
            'id' => $m->id,
            'url' => $m->url,
            'thumb' => $m->url,
            'alt_text' => $m->alt_text,
            'original_name' => $m->original_name,
            'mime_type' => $m->mime_type,
            'width' => $m->width,
            'height' => $m->height,
            'is_featured' => (bool) $m->is_featured,
            'collection' => $m->collection,
        ]);

        return response()->json(['data' => $items]);
    }

    /**
     * POST /api/media/upload
     * Upload a new media file (for picker).
     */
    public function upload(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,avif,svg',
            'mediable_type' => 'required|string',
            'mediable_id' => 'required|integer',
            'collection' => 'required|string|max:64',
            'alt_text' => 'nullable|string|max:255',
            'is_featured' => 'sometimes|boolean',
        ]);

        $modelClass = $data['mediable_type'];
        $model = $modelClass::find($data['mediable_id']);
        if (!$model) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        $media = $this->media->upload(
            model: $model,
            collection: $data['collection'],
            source: $request->file('file'),
            altText: $data['alt_text'] ?? null,
            isFeatured: $request->boolean('is_featured'),
        );

        return response()->json([
            'id' => $media->id,
            'url' => $media->url,
            'alt_text' => $media->alt_text,
            'is_featured' => (bool) $media->is_featured,
        ]);
    }

    /**
     * GET /api/media/{id}
     */
    public function show(Media $medium): JsonResponse
    {
        return response()->json([
            'id' => $medium->id,
            'url' => $medium->url,
            'alt_text' => $medium->alt_text,
            'mime_type' => $medium->mime_type,
            'width' => $medium->width,
            'height' => $medium->height,
            'is_featured' => (bool) $medium->is_featured,
        ]);
    }
}
