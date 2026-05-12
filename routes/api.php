<?php

use App\Http\Controllers\Api\V1\Listing\CategoriesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/test-create-123', function(Request $request) {
    \Log::info('=== SIMPLE TEST ROUTE HIT ===');
    \Log::info('User: ' . (auth()->user()?->id ?? 'null'));
    \Log::info('Data: ', $request->all());
    
    return response()->json([
        'message' => 'Simple test route works!',
        'user_id' => auth()->user()?->id,
        'received' => $request->all()
    ]);
})->middleware('auth:sanctum');


Route::post('/v1/listing/auth/products/create-debug-full', function(Request $request) {
    try {
        \Log::info('=== FULL DEBUG START ===');
        \Log::info('Method: ' . $request->method());
        \Log::info('Has auth: ' . ($request->bearerToken() ? 'YES' : 'NO'));
        \Log::info('User ID: ' . (auth()->user()?->id ?? 'null'));
        \Log::info('All request data: ', $request->all());
        \Log::info('Has files: ' . ($request->hasFile('images') ? 'YES' : 'NO'));
        
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $idx => $file) {
                \Log::info("Image $idx: " . $file->getClientOriginalName() . ", Size: " . $file->getSize());
            }
        }
        
        // Try to call the actual controller
        $controller = app(\App\Http\Controllers\Api\V1\Listing\ProductController::class);
        $result = $controller->store($request);
        
        \Log::info('=== CONTROLLER RETURNED SUCCESS ===');
        return $result;
        
    } catch (\Throwable $e) {
        \Log::error('=== FULL DEBUG ERROR ===');
        \Log::error('Message: ' . $e->getMessage());
        \Log::error('File: ' . $e->getFile());
        \Log::error('Line: ' . $e->getLine());
        \Log::error('Trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware('auth:sanctum');

// Debug all requests to /api/v1/listing/auth/products/create
Route::match(['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'], '/v1/listing/auth/products/create', function(Request $request) {
    try {
        \Log::info('=== ROUTE HIT ===', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'user_id' => auth()->user()?->id,
            'has_token' => $request->bearerToken() ? true : false,
        ]);
        
        return response()->json([
            'message' => 'Route is working!',
            'method' => $request->method(),
            'user_id' => auth()->user()?->id,
            'headers_received' => true
        ]);
    } catch (\Exception $e) {
        \Log::error('Route debug error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::post('/debug-create-listing', function(Request $request) {
    try {
        $user = auth()->user();
        \Log::info('Debug listing attempt', [
            'user_id' => $user?->id,
            'has_address' => $user?->addresses()->exists(),
            'request_data' => $request->all()
        ]);
        
        return response()->json([
            'message' => 'Debug endpoint reached',
            'user_id' => $user?->id
        ]);
    } catch (\Exception $e) {
        \Log::error('Debug error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
})->middleware('auth:sanctum');

Route::get('/logs-last-10min', function() {
    $logFile = storage_path('logs/laravel.log');
    
    if (!file_exists($logFile)) {
        return response()->json(['error' => 'Log file not found at: ' . $logFile], 404);
    }
    
    // Get the timestamp for 10 minutes ago
    $tenMinutesAgo = now()->subMinutes(10);
    
    // Read the entire log file
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    
    $recentLogs = [];
    $currentLogEntry = '';
    $logTimestamp = null;
    
    foreach ($lines as $line) {
        // Check if line contains a timestamp pattern like [2026-05-11 10:30:00]
        if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $line, $matches)) {
            // If we have a previous log entry, check if it's within 10 minutes
            if ($currentLogEntry && $logTimestamp) {
                if ($logTimestamp >= $tenMinutesAgo) {
                    $recentLogs[] = $currentLogEntry;
                }
            }
            // Start new log entry
            $currentLogEntry = $line;
            $logTimestamp = \Carbon\Carbon::parse($matches[1]);
        } else {
            // Append to current log entry
            $currentLogEntry .= "\n" . $line;
        }
    }
    
    // Check the last log entry
    if ($currentLogEntry && $logTimestamp && $logTimestamp >= $tenMinutesAgo) {
        $recentLogs[] = $currentLogEntry;
    }
    
    // Get only the last 50 lines from recent logs
    $recentLogs = array_slice($recentLogs, -50);
    
    return response()->json([
        'success' => true,
        'time_range' => 'Last 10 minutes',
        'from_time' => $tenMinutesAgo->toDateTimeString(),
        'to_time' => now()->toDateTimeString(),
        'total_lines_found' => count($recentLogs),
        'logs' => $recentLogs
    ]);
});


Route::get('/last-error', function() {
    $logFile = storage_path('logs/laravel.log');
    
    if (!file_exists($logFile)) {
        return response()->json(['error' => 'Log file not found'], 404);
    }
    
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    $errorLines = [];
    
    // Find all error lines
    foreach ($lines as $line) {
        if (str_contains($line, 'ERROR') || 
            str_contains($line, 'error') ||
            str_contains($line, 'Exception') ||
            str_contains($line, 'Fatal')) {
            $errorLines[] = $line;
        }
    }
    
    // Get last 10 errors
    $lastErrors = array_slice($errorLines, -10);
    
    return response()->json([
        'total_errors_found' => count($errorLines),
        'last_errors' => $lastErrors
    ]);
});

Route::get('/view-laravel-logs', function() {
    $logFile = storage_path('logs/laravel.log');
    if (!file_exists($logFile)) {
        return response()->json(['error' => 'Log file not found'], 404);
    }
    
    // Get last 100 lines
    $logs = [];
    $file = new \SplFileObject($logFile);
    $file->seek(PHP_INT_MAX);
    $totalLines = $file->key();
    
    $startLine = max(0, $totalLines - 100);
    for ($i = $startLine; $i <= $totalLines; $i++) {
        $file->seek($i);
        $line = $file->current();
        if (str_contains($line, 'OTP') || str_contains($line, 'otp')) {
            $logs[] = $line;
        }
    }
    
    return response()->json(['otp_logs' => $logs]);
});

Route::get('/test', function() {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now(),
        'status' => 'success'
    ]);
});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {

    // DEBUG: Direct database query for products
    Route::get('/debug-products-direct', function() {
        try {
            $products = DB::table('products')
                ->where('approval_status', 'approved')
                ->whereNull('deleted_at')
                ->select('id', 'title', 'price', 'approval_status')
                ->get();
            
            return response()->json([
                'success' => true,
                'count' => $products->count(),
                'products' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    });
    
    require base_path('routes/api-group/auth/auth.php');
    require base_path('routes/api-group/auth/social-auth.php');
    require base_path('routes/api-group/user/preferences.php');
    Route::prefix('listing')->group(function () {
        foreach (glob(base_path('routes/api-group/listing/*.php')) as $file) {
            require $file;
        }
    });
    require base_path('routes/api-group/user/address.php');
    require base_path('routes/api-group/user/followers.php');
    require base_path('routes/api-group/user/ratings.php');
    require base_path('routes/api-group/user/shop.php');
    require base_path('routes/api-group/user/users.php');
    require base_path('routes/api-group/user/reviews.php');
    require base_path('routes/api-group/cart/cart.php');
    require base_path('routes/api-group/cart/checkout.php');
    require base_path('routes/api-group/conversation/conversations.php');
    require base_path('routes/api-group/user/bank.php');
    require base_path('routes/api-group/activity/activity.php');
    require base_path('routes/api-group/admin/admin-apis.php');
});
