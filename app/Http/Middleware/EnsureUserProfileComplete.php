<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class EnsureUserProfileComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Allow if not authenticated (let auth middleware handle this)
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Define which fields must be present
        $incomplete =
            !$user->first_name ||
            !$user->last_name ||
            !$user->email ||
            !$user->password;

        // Allow access only to this specific route
        $allowedPaths = [
            'api/v1/auth/verify-otp',
            'api/v1/user/update-profile',
            'api/v1/seller/edit', // whatever your frontend calls to complete the profile
        ];

        $requestPath = ltrim($request->path(), '/');

        if ($incomplete && !in_array($requestPath, $allowedPaths)) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your profile to proceed.',
                'redirect_url' => '/seller/edit',
            ], 403);
        }

        return $next($request);
    }
}
