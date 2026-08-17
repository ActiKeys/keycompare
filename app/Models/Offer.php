<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'store_id',
        'link',
        'price',
        'currency',
        'region',
        'in_stock',
        'raw_data',
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'in_stock' => 'boolean',
        'raw_data' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
