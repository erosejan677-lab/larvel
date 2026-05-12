<?php

use App\Http\Controllers\Api\V1\Listing\CategoriesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


Route::post('/test-product-controller', function(Request $request) {
    try {
        // Create the correct request type
        $createRequest = \App\Http\Requests\Api\V1\Listing\CreateProductRequest::createFrom($request);
        
        $controller = app(\App\Http\Controllers\Api\V1\Listing\ProductController::class);
        return $controller->store($createRequest);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage(), 'line' => $e->getLine()], 500);
    }
})->middleware('auth:sanctum');

// ========== DEBUG ROUTES (Keep for testing) ==========

Route::post('/debug-received-data', function(Request $request) {
    return response()->json([
        'all_data' => $request->all(),
        'has_city' => $request->has('city'),
        'city_value' => $request->input('city'),
        'files' => $request->hasFile('images') ? count($request->file('images')) : 0
    ]);
})->middleware('auth:sanctum');

Route::post('/test-product-service', function(Request $request) {
    try {
        $user = auth()->user();
        $data = $request->all();
        $images = $request->file('images', []);
        
        $brand = \App\Models\Brand::firstOrCreate(['name' => $data['brand_name']]);
        
        $product = \App\Models\Product::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'category_id' => $data['category_id'],
            'brand_id' => $brand->id,
            'condition_id' => $data['condition_id'],
            'address_id' => $data['address_id'],
            'city' => $data['city'] ?? null,
            'size' => $data['size'] ?? null,
            'color' => $data['color'] ?? null,
            'location' => $data['location'] ?? null,
            'shipping_type' => $data['shipping_type'] ?? 'depopShipping',
            'quantity_left' => 1,
            'quantity' => 1,
            'approval_status' => 'pending',
            'active' => true,
            'sold' => false,
            'allow_offers' => true
        ]);
        
        foreach ($images as $image) {
            $path = $image->store('products', 'public');
            $product->photos()->create(['image_path' => asset('storage/' . $path)]);
        }
        
        return response()->json(['success' => true, 'product' => $product]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
})->middleware('auth:sanctum');

Route::post('/test-create-123', function(Request $request) {
    return response()->json([
        'message' => 'Simple test route works!',
        'user_id' => auth()->user()?->id,
        'received' => $request->all()
    ]);
})->middleware('auth:sanctum');

Route::post('/debug-create-listing', function(Request $request) {
    $user = auth()->user();
    return response()->json([
        'message' => 'Debug endpoint reached',
        'user_id' => $user?->id
    ]);
})->middleware('auth:sanctum');

// ========== LOG ROUTES ==========
Route::get('/logs-last-10min', function() {
    $logFile = storage_path('logs/laravel.log');
    if (!file_exists($logFile)) {
        return response()->json(['error' => 'Log file not found'], 404);
    }
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    $lastLines = array_slice($lines, -200);
    return response()->json(['logs' => $lastLines]);
});

Route::get('/last-error', function() {
    $logFile = storage_path('logs/laravel.log');
    if (!file_exists($logFile)) {
        return response()->json(['error' => 'Log file not found'], 404);
    }
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    $errorLines = [];
    foreach ($lines as $line) {
        if (str_contains($line, 'ERROR') || str_contains($line, 'Exception')) {
            $errorLines[] = $line;
        }
    }
    return response()->json(['last_errors' => array_slice($errorLines, -10)]);
});

Route::get('/test', function() {
    return response()->json(['message' => 'API is working!', 'timestamp' => now()]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ========== MAIN API ROUTES ==========
Route::prefix('v1')->group(function () {
    
    // DIRECT PRODUCT CREATE ROUTE (Fix for your issue)
    Route::post('/listing/auth/products/create', [App\Http\Controllers\Api\V1\Listing\ProductController::class, 'store'])
        ->middleware('auth:sanctum');
    
    Route::get('/debug-products-direct', function() {
        try {
            $products = DB::table('products')
                ->where('approval_status', 'approved')
                ->whereNull('deleted_at')
                ->select('id', 'title', 'price', 'approval_status')
                ->get();
            return response()->json(['success' => true, 'products' => $products]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
