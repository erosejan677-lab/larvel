<?php

use Illuminate\Support\Facades\Route;

// THE SIMPLEST POSSIBLE API ROUTE
Route::get('/ping', function() {
    return response()->json(['message' => 'pong']);
});