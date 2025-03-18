<?php

use App\Http\Controllers\Api\V1\Listing\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth/products')->middleware(['auth:sanctum', 'role:user'])->group(function() {
    Route::post('create', [ProductController::class, 'store']);
    Route::get('show', [ProductController::class, 'show']);
});

Route::get('public/products/show', [ProductController::class, 'publicProducts']);
