<?php

use App\Http\Controllers\Api\V1\ConditionController;
use Illuminate\Support\Facades\Route;

Route::prefix('conditions')->group(function () {
    Route::get('/', [ConditionController::class, 'index']);
    Route::get('{id}', [ConditionController::class, 'show']);
});
