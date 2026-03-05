<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API v1 routes
Route::prefix('v1')->group(function () {
    
    // Load all route files
    require_once base_path('routes/api-group/auth/auth.php');
    require_once base_path('routes/api-group/auth/social-auth.php');
    require_once base_path('routes/api-group/user/preferences.php');
    
    Route::prefix('listing')->group(function () {
        $files = glob(base_path('routes/api-group/listing/*.php'));
        foreach ($files as $file) {
            require_once $file;
        }
    });
    
    require_once base_path('routes/api-group/user/address.php');
    require_once base_path('routes/api-group/user/followers.php');
    require_once base_path('routes/api-group/user/ratings.php');
    require_once base_path('routes/api-group/user/shop.php');
    require_once base_path('routes/api-group/user/users.php');
    require_once base_path('routes/api-group/user/reviews.php');
    require_once base_path('routes/api-group/cart/cart.php');
    require_once base_path('routes/api-group/cart/checkout.php');
    require_once base_path('routes/api-group/conversation/conversations.php');
    require_once base_path('routes/api-group/user/bank.php');
    require_once base_path('routes/api-group/activity/activity.php');
    require_once base_path('routes/api-group/admin/admin-apis.php');
});