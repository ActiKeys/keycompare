<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'link',
        'name',
        'image_link',
        'description',
        'platform',
        'category',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * All media (images, files) attached to this product.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Only images in the 'featured' collection (the main product image).
     */
    public function featuredImage()
    {
        return $this->morphOne(Media::class, 'mediable')
            ->where('collection', 'featured')
            ->where('is_featured', true);
    }

    /**
     * All images (any collection).
     */
    public function images()
    {
        return $this->morphMany(Media::class, 'mediable')
            ->where('mime_type', 'like', 'image/%')
            ->orderBy('sort_order');
    }

    /**
     * Get the best (lowest) in-stock offer.
     */
    public function getBestOfferAttribute(): ?Offer
    {
        return $this->offers()
            ->where('in_stock', true)
            ->orderBy('price')
            ->first();
    }

    /**
     * Get offer count.
     */
    public function getOfferCountAttribute(): int
    {
        return $this->offers()->count();
    }

    /**
     * URL to display the product's featured image (or external fallback).
     */
    public function getDisplayImageAttribute(): ?string
    {
        $media = $this->featuredImage()->first();
        if ($media) {
            return $media->url;
        }
        // Fallback to image_link from import (if we couldn't download it)
        return $this->image_link;
    }
}
