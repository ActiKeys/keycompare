<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Installer (only accessible when not installed)
Route::middleware('not_installed')->prefix('install')->name('installer.')->group(function () {
    Route::get('/', [InstallerController::class, 'index']);
    Route::get('/welcome', [InstallerController::class, 'welcome'])->name('welcome');
    Route::get('/database', [InstallerController::class, 'database'])->name('database');
    Route::post('/database', [InstallerController::class, 'saveDatabase'])->name('save_database');
    Route::post('/database/test', [InstallerController::class, 'testDatabase'])->name('test_database');
    Route::get('/admin', [InstallerController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallerController::class, 'saveAdmin'])->name('save_admin');
    Route::get('/settings', [InstallerController::class, 'settings'])->name('settings');
    Route::post('/settings', [InstallerController::class, 'saveSettings'])->name('save_settings');
    Route::get('/done', [InstallerController::class, 'done'])->name('done');
});

// Public site
Route::get('/', [ProductController::class, 'home'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::get('/search', [ProductController::class, 'index'])->name('search');

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin
Route::prefix('admin')->name('admin.')->group(base_path('routes/admin.php'));
