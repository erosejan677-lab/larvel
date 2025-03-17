<?php

use App\Http\Controllers\Api\V1\Listing\BrandController;
use Illuminate\Support\Facades\Route;

Route::get('brands', [BrandController::class, 'index']);
