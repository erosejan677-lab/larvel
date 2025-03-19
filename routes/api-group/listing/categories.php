<?php


use App\Http\Controllers\Api\V1\Listing\CategoriesController;
use Illuminate\Support\Facades\Route;

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoriesController::class, 'index']);
    Route::get('{id}', [CategoriesController::class, 'show']);
});
