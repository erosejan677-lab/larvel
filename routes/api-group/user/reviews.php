<?php

use App\Http\Controllers\Api\V1\User\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')->middleware(['auth:sanctum', 'role:user'])->group(function () {
    // Create or update a review (using POST for idempotent updateOrCreate)
    Route::post('{userId}/review', [ReviewController::class, 'store']);
    // Update review (alternatively, you can use PUT if you prefer)
    Route::put('{userId}/review', [ReviewController::class, 'update']);
    // Delete review
    Route::delete('{userId}/review', [ReviewController::class, 'destroy']);
});

// Public endpoint to view all reviews for a given user.
Route::get('users/{userId}/reviews', [ReviewController::class, 'getReviews']);
