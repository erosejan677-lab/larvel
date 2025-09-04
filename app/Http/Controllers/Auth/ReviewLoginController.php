<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ReviewLoginToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ReviewLoginController extends Controller
{
    // POST /api/v1/auth/review-login { token: string }
    public function exchange(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        $plain = $request->string('token');

        $hashed = hash('sha256', $plain);
        $record = ReviewLoginToken::with('user')->where('token', $hashed)->first();

        if (!$record || !$record->isValid()) {
            return response()->json(['message' => 'Invalid or expired token'], 422);
        }

        // mark used
        $record->update(['used_at' => now()]);

        // issue API token (Sanctum/Passport/JWT – example uses Sanctum/PAT)
        $apiToken = $record->user->createToken('review-login')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $apiToken,
                'user'  => [
                    'id'       => $record->user->id,
                    'email'    => $record->user->email,
                    'name'     => $record->user->name,
                ],
            ],
        ]);
    }

    // GET /api/v1/auth/review-login/redirect/{token}?redirect=/user/3
    public function redirectLogin(Request $request, string $token)
    {
        $record = ReviewLoginToken::with('user')->where('token', $token)->first();

        if (!$record || !$record->isValid()) {
            return response('Invalid or expired token', 410);
        }

        $record->update(['used_at' => now()]);
        $user = $record->user;

        // Optional extras
        $user->markEmailAsVerified();
        $user->assignRole('user');
        Auth::login($user);

        // Issue SPA token
        $apiToken = $user->createToken("User.{$user->id}.AuthToken")->plainTextToken;

        // Frontend base from config
        $frontBase = rtrim(config('services.frontend_url'), '/');

        // Path from query, default as needed
        $path = $request->query('redirect');

        // 🛡️ sanitize to avoid open-redirects (only allow internal paths like /user/123 or /some/route)
        if (!is_string($path) || !preg_match('#^/[A-Za-z0-9/_-]*$#', $path)) {
            $path = '/';
        }

        // Build final URL and append api_token
        $redirectUrl = $frontBase . $path;

        $sep = parse_url($redirectUrl, PHP_URL_QUERY) ? '&' : '?';

        return redirect()->away($redirectUrl . $sep . 'api_token=' . urlencode($apiToken));
    }

}
