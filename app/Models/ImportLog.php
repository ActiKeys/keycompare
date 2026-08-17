<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $fillable = [
        'source',
        'status',
        'products_count',
        'offers_count',
        'products_created',
        'products_updated',
        'offers_created',
        'offers_updated',
        'errors',
        'payload',
        'duration_ms',
    ];

    protected $casts = [
        'errors' => 'array',
        'payload' => 'array',
    ];
}
