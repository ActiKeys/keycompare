<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class MediaService
{
    /**
     * Upload a file from a local path or UploadedFile and attach it to a model.
     *
     * @param Model $model     The model to attach to (must use morph relation)
     * @param string $collection  'featured', 'gallery', 'icon', etc.
     * @param UploadedFile|string $source  UploadedFile or local file path
     * @param string|null $altText   Alt text for accessibility
     * @param string|null $title
     * @param string|null $caption
     * @param bool $isFeatured   Mark as featured (only one per collection per model)
     *
     * @return Media
     */
    public function upload(
        Model $model,
        string $collection,
        UploadedFile|string $source,
        ?string $altText = null,
        ?string $title = null,
        ?string $caption = null,
        bool $isFeatured = false,
        ?string $sourceUrl = null,
    ): Media {
        if ($isFeatured) {
            $this->clearFeatured($model, $collection);
        }

        if ($source instanceof UploadedFile) {
            $path = $source->store($this->pathFor($model), 'public');
            $absolutePath = Storage::disk('public')->path($path);
            $originalName = $source->getClientOriginalName();
            $mime = $source->getMimeType();
            $size = $source->getSize();
        } else {
            // Local file path
            if (!file_exists($source)) {
                throw new \InvalidArgumentException("File not found: {$source}");
            }
            $absolutePath = $source;
            $path = $this->pathFor($model) . '/' . basename($source);
            $publicPath = Storage::disk('public')->putFileAs(
                $this->pathFor($model),
                new SymfonyFile($source),
                basename($source)
            );
            $path = $publicPath;
            $originalName = basename($source);
            $mime = mime_content_type($source) ?: 'application/octet-stream';
            $size = filesize($source);
        }

        $dimensions = $this->getImageDimensions($absolutePath, $mime);

        return Media::create([
            'mediable_type' => get_class($model),
            'mediable_id' => $model->id,
            'collection' => $collection,
            'disk' => 'public',
            'file_path' => $path,
            'original_name' => $originalName,
            'mime_type' => $mime,
            'size' => $size,
            'width' => $dimensions['width'] ?? null,
            'height' => $dimensions['height'] ?? null,
            'alt_text' => $altText,
            'title' => $title,
            'caption' => $caption,
            'is_featured' => $isFeatured,
            'source_url' => $sourceUrl,
        ]);
    }

    /**
     * Download a remote image (e.g. from import) and attach to a model.
     * Skips if URL is empty or already attached.
     */
    public function downloadAndAttach(
        Model $model,
        string $collection,
        string $url,
        ?string $altText = null,
        bool $isFeatured = false,
    ): ?Media {
        if (empty($url)) {
            return null;
        }

        // Check if we already have this image attached (by source URL)
        $existing = Media::where('mediable_type', get_class($model))
            ->where('mediable_id', $model->id)
            ->where('source_url', $url)
            ->first();
        if ($existing) {
            // Promote to featured if requested
            if ($isFeatured && !$existing->is_featured) {
                $this->clearFeatured($model, $collection);
                $existing->update(['is_featured' => true]);
            }
            return $existing;
        }

        try {
            $response = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'KeyCompare/1.0 (+https://github.com/ActiKeys/keycompare)'])
                ->withOptions(['verify' => (bool) config('keycompare.media.verify_ssl', true)])
                ->get($url);

            if (!$response->successful()) {
                Log::warning('media_download_failed', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $contentType = $response->header('Content-Type');
            if (!$contentType || !str_starts_with($contentType, 'image/')) {
                Log::warning('media_not_image', ['url' => $url, 'content_type' => $contentType]);
                return null;
            }

            // Save to temp file
            $ext = $this->extensionFromMime($contentType);
            $tempPath = tempnam(sys_get_temp_dir(), 'kc_media_') . '.' . $ext;
            file_put_contents($tempPath, $response->body());

            $media = $this->upload(
                model: $model,
                collection: $collection,
                source: $tempPath,
                altText: $altText,
                isFeatured: $isFeatured,
                sourceUrl: $url,
            );

            @unlink($tempPath);
            return $media;
        } catch (\Throwable $e) {
            Log::warning('media_download_error', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Delete a media item (removes file + DB record).
     */
    public function delete(Media $media): bool
    {
        if (Storage::disk($media->disk)->exists($media->file_path)) {
            Storage::disk($media->disk)->delete($media->file_path);
        }
        return $media->delete();
    }

    /**
     * Mark a different media as featured (within the same model + collection).
     */
    public function setFeatured(Media $media): Media
    {
        $this->clearFeatured($media->mediable, $media->collection);
        $media->update(['is_featured' => true]);
        return $media;
    }

    /**
     * Clear is_featured for all media in a collection on a model.
     */
    public function clearFeatured(Model $model, string $collection): void
    {
        Media::where('mediable_type', get_class($model))
            ->where('mediable_id', $model->id)
            ->where('collection', $collection)
            ->where('is_featured', true)
            ->update(['is_featured' => false]);
    }

    /**
     * Build a storage path for a model (e.g. media/products/2026/08).
     */
    protected function pathFor(Model $model): string
    {
        $class = class_basename($model);
        $slug = Str::slug($class);
        $id = $model->id ?? 'new';
        return "media/{$slug}/{$id}";
    }

    /**
     * Try to get image dimensions (jpg, png, gif, webp).
     */
    protected function getImageDimensions(string $path, ?string $mime): array
    {
        if (!$mime || !str_starts_with($mime, 'image/')) {
            return ['width' => null, 'height' => null];
        }
        try {
            $info = @getimagesize($path);
            if ($info) {
                return ['width' => $info[0], 'height' => $info[1]];
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return ['width' => null, 'height' => null];
    }

    protected function extensionFromMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
            'image/svg+xml' => 'svg',
            default => 'bin',
        };
    }
}
