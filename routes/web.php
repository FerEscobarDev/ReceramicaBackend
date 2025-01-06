<?php

use App\Http\Controllers\Products;
use App\Http\Controllers\ProductsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/creaciones', [
        ProductsController::class, 'index'
    ])->name('creaciones');
    Route::get('/creaciones/create', [
        ProductsController::class, 'create'
    ])->name('creaciones.create');
    Route::post('/creaciones', [
        ProductsController::class, 'store'
    ])->name('creaciones.store');

    Route::delete('/creaciones/delete/{product}', [
        ProductsController::class, 'destroy'
    ])->name('creaciones.destroy');

    Route::get('/creaciones/update/{product}', [
        ProductsController::class, 'edit'
    ])->name('creaciones.edit');
    Route::post('/creaciones/update/{product}', [
        ProductsController::class, 'update'
    ])->name('creaciones.update');
    Route::get('/creaciones/images', [
        ProductsController::class, 'viewImages'
    ])->name('creaciones.images');
    Route::post('creaciones/images/optimize', [
        ProductsController::class, 'optimizeImages'
    ])->name('creaciones.images.optimize');
});
