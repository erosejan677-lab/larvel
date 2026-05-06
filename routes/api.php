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



Route::post('/debug/register-try', function(Request $request) {
    try {
        // Try to create a user directly
        $user = new \App\Models\User();
        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        $user->first_name = $request->first_name ?? null;
        $user->last_name = $request->last_name ?? null;
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
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
