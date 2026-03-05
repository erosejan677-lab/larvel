<?php

Route::get('/test-simple', function() {
    return response()->json(['message' => 'This route works!']);
});