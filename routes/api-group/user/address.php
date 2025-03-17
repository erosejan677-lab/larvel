<?php

use App\Http\Controllers\Api\V1\AddressController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->middleware(['auth:sanctum', 'role:user'])->group(function () {
    Route::post('address', [AddressController::class, 'store']);
    Route::get('address', [AddressController::class, 'show']);
});
