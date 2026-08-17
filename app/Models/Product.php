<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
