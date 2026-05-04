<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;  // ← ADD THIS LINE at the top with other use statements

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Your existing routes...
Route::get('/phpinfo', fn () => response()->json([
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'max_file_uploads' => ini_get('max_file_uploads'),
]));

Route::get('/phpinfo-path', function () {
    ob_start();
    phpinfo(INFO_GENERAL);
    $html = ob_get_clean();
    return response($html);
});

// ← ADD THIS NEW ROUTE AT THE BOTTOM
Route::get('/db-test', function() {
    try {
        DB::connection()->getPdo();
        return "Database connection is successful!";
    } catch (\Exception $e) {
        return "Database error: " . $e->getMessage();
    }
});
