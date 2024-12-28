<?php

use App\Http\Controllers\ProductsController;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return response()->json($request->user());
})->middleware('auth:sanctum');

Route::middleware([
    'auth:sanctum'
])->group(function () {
    Route::get('/creaciones', function() {
        return response()->json(Product::all()->load('images'));
    })->name('creacionesFront');
    Route::get('/images', function() {
        return response()->json(Image::where('main', 1)->whereHas('product', function($query) {
                $query->where('landing', 1);
            })->get());
    })->name('creacionesFront');
    Route::post('/uploadImages', [ProductsController::class, 'uploadImages']);
});