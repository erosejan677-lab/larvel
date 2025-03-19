<?php

use App\Http\Controllers\Api\V1\Listing\BrandController;
use Illuminate\Support\Facades\Route;

Route::prefix('brands')->group(function () {
    Route::get('/', [BrandController::class, 'index']);
    Route::get('{id}', [BrandController::class, 'show']);

});
