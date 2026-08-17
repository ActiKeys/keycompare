<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'home'])->name('home');

// Products
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');

// Search
Route::get('/search', [ProductController::class, 'index'])->name('search');

// Filament admin (under /admin)
Route::prefix('admin')->middleware([])->group(base_path('routes/admin.php'));
