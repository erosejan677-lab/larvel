<?php


use App\Http\Controllers\Api\V1\Listing\CategoriesController;
use Illuminate\Support\Facades\Route;

Route::get('categories', [CategoriesController::class, 'index']);
