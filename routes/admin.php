<?php

use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/', function () {
        return redirect()->route('admin.products.index');
    })->name('dashboard');

    // Media
    Route::prefix('media')->name('media.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::get('/create', [MediaController::class, 'create'])->name('create');
        Route::post('/', [MediaController::class, 'store'])->name('store');
        Route::get('/{medium}', [MediaController::class, 'show'])->name('show');
        Route::put('/{medium}', [MediaController::class, 'update'])->name('update');
        Route::delete('/{medium}', [MediaController::class, 'destroy'])->name('destroy');
    });

    // Products
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
    });

    Route::get('/import-logs', function () {
        $logs = \App\Models\ImportLog::orderBy('id', 'desc')->limit(50)->get();
        return view('admin.import-logs', compact('logs'));
    })->name('import-logs.index');
});
