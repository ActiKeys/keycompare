<?php

use App\Http\Controllers\Api\MediaApiController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

// JSON import endpoint
Route::post('/import', [ImportController::class, 'import'])->name('import.api');

// Public JSON API
Route::get('/products', function () {
    return \App\Models\Product::with('offers.store')->orderBy('updated_at', 'desc')->limit(50)->get();
});

Route::get('/products/{id}', function ($id) {
    return \App\Models\Product::with('offers.store')->findOrFail($id);
});

Route::get('/stats', function () {
    return [
        'products' => \App\Models\Product::count(),
        'offers' => \App\Models\Offer::count(),
        'stores' => \App\Models\Store::count(),
    ];
});

// Media API (used by image picker)
Route::prefix('media')->group(function () {
    Route::get('/', [MediaApiController::class, 'index']);
    Route::post('/upload', [MediaApiController::class, 'upload']);
    Route::get('/{medium}', [MediaApiController::class, 'show']);
});
