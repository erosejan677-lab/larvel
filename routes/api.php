<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes - DIAGNOSTIC VERSION
|--------------------------------------------------------------------------
*/

// Simple test route
Route::get('/laravel-test', function () {
    return response()->json([
        'message' => 'Base API is working!'
    ]);
});

// Try loading each file one by one with error handling
Route::prefix('v1')->group(function () {
    
    // Test auth file
    try {
        require_once base_path('routes/api-group/auth/auth.php');
        echo "Loaded: auth.php\n";
    } catch (\Exception $e) {
        echo "Error in auth.php: " . $e->getMessage() . "\n";
    }
    
    // Test social-auth file
    try {
        require_once base_path('routes/api-group/auth/social-auth.php');
        echo "Loaded: social-auth.php\n";
    } catch (\Exception $e) {
        echo "Error in social-auth.php: " . $e->getMessage() . "\n";
    }
    
    // Test preferences file
    try {
        require_once base_path('routes/api-group/user/preferences.php');
        echo "Loaded: preferences.php\n";
    } catch (\Exception $e) {
        echo "Error in preferences.php: " . $e->getMessage() . "\n";
    }
    
    // For listing files
    Route::prefix('listing')->group(function () {
        $files = glob(base_path('routes/api-group/listing/*.php'));
        foreach ($files as $file) {
            try {
                require_once $file;
                echo "Loaded: " . basename($file) . "\n";
            } catch (\Exception $e) {
                echo "Error in " . basename($file) . ": " . $e->getMessage() . "\n";
            }
        }
    });
    
    // Test address file
    try {
        require_once base_path('routes/api-group/user/address.php');
        echo "Loaded: address.php\n";
    } catch (\Exception $e) {
        echo "Error in address.php: " . $e->getMessage() . "\n";
    }
    
    // Test followers file
    try {
        require_once base_path('routes/api-group/user/followers.php');
        echo "Loaded: followers.php\n";
    } catch (\Exception $e) {
        echo "Error in followers.php: " . $e->getMessage() . "\n";
    }
    
    // Test ratings file
    try {
        require_once base_path('routes/api-group/user/ratings.php');
        echo "Loaded: ratings.php\n";
    } catch (\Exception $e) {
        echo "Error in ratings.php: " . $e->getMessage() . "\n";
    }
    
    // Test shop file
    try {
        require_once base_path('routes/api-group/user/shop.php');
        echo "Loaded: shop.php\n";
    } catch (\Exception $e) {
        echo "Error in shop.php: " . $e->getMessage() . "\n";
    }
    
    // Test users file
    try {
        require_once base_path('routes/api-group/user/users.php');
        echo "Loaded: users.php\n";
    } catch (\Exception $e) {
        echo "Error in users.php: " . $e->getMessage() . "\n";
    }
    
    // Test reviews file
    try {
        require_once base_path('routes/api-group/user/reviews.php');
        echo "Loaded: reviews.php\n";
    } catch (\Exception $e) {
        echo "Error in reviews.php: " . $e->getMessage() . "\n";
    }
    
    // Test cart file
    try {
        require_once base_path('routes/api-group/cart/cart.php');
        echo "Loaded: cart.php\n";
    } catch (\Exception $e) {
        echo "Error in cart.php: " . $e->getMessage() . "\n";
    }
    
    // Test checkout file
    try {
        require_once base_path('routes/api-group/cart/checkout.php');
        echo "Loaded: checkout.php\n";
    } catch (\Exception $e) {
        echo "Error in checkout.php: " . $e->getMessage() . "\n";
    }
    
    // Test conversation file
    try {
        require_once base_path('routes/api-group/conversation/conversations.php');
        echo "Loaded: conversations.php\n";
    } catch (\Exception $e) {
        echo "Error in conversations.php: " . $e->getMessage() . "\n";
    }
    
    // Test bank file
    try {
        require_once base_path('routes/api-group/user/bank.php');
        echo "Loaded: bank.php\n";
    } catch (\Exception $e) {
        echo "Error in bank.php: " . $e->getMessage() . "\n";
    }
    
    // Test activity file
    try {
        require_once base_path('routes/api-group/activity/activity.php');
        echo "Loaded: activity.php\n";
    } catch (\Exception $e) {
        echo "Error in activity.php: " . $e->getMessage() . "\n";
    }
    
    // Test admin file
    try {
        require_once base_path('routes/api-group/admin/admin-apis.php');
        echo "Loaded: admin-apis.php\n";
    } catch (\Exception $e) {
        echo "Error in admin-apis.php: " . $e->getMessage() . "\n";
    }
});