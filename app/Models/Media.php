<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'collection',
        'disk',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'width',
        'height',
        'alt_text',
        'title',
        'caption',
        'sort_order',
        'is_featured',
        'source_url',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_featured' => 'boolean',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Polymorphic relation: this media belongs to any model.
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Public URL to display the image.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }

    /**
     * Relative path (used by Storage::url for the configured filesystem).
     */
    public function getPathAttribute(): string
    {
        return $this->file_path;
    }

    /**
     * Check if this is an image (vs PDF, video, etc.).
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /**
     * Human-readable file size.
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size ?? 0;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    /**
     * Scope: only images.
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Scope: only in a specific collection.
     */
    public function scopeInCollection($query, string $collection)
    {
        return $query->where('collection', $collection);
    }
}
