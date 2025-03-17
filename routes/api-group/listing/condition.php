<?php

use App\Http\Controllers\Api\V1\ConditionController;
use Illuminate\Support\Facades\Route;


Route::get('conditions', [ConditionController::class, 'index']);
