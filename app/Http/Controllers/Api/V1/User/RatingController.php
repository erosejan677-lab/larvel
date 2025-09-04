<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Helpers\ActivityLogHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Rating;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RatingController extends Controller
{
    use ApiResponse;

    /**
     * Rate a user (authenticated users only).
     */
    public function rateUser(Request $request, int $userId)
    {
        // must be authenticated (protect this route with auth:sanctum or similar)
        $rater = $request->user();
        if ($rater->id === $userId) {
            return $this->errorResponse('You cannot rate yourself', 403);
        }

        $hasPurchased = Order::query()
            ->where('seller_id', $userId)
            ->where(function ($q) use ($rater) {
                $q->where('buyer_id', $rater->id);
                if (!empty($rater->email)) {
                    $q->orWhereRaw('LOWER(guest_email) = ?', [strtolower($rater->email)]);
                }
            })
            ->exists();

        if (!$hasPurchased) {
            return $this->errorResponse('Only customers who purchased from this seller can leave a rating.', 403);
        }

        // Validate input
        $validated = $request->validate([
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'required|string|max:2000',
            'pictures'   => 'sometimes|array|min:1|max:3',
            'pictures.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Create/update single rating per rater→seller
        $rating = Rating::updateOrCreate(
            ['user_id' => $userId, 'rater_id' => $rater->id],
            ['rating'  => $validated['rating'], 'comment' => $validated['comment']]
        );

        // Reset existing pictures for this rating
        $rating->pictures()->delete();

        // Store new pictures (optional)
        if ($request->hasFile('pictures')) {
            foreach ($request->file('pictures') as $picture) {
                $filename = time().'_'.$picture->getClientOriginalName();
                $relativePath = $picture->storeAs('ratings', $filename, 'public');
                $fullUrl = asset(Storage::url($relativePath));

                $rating->pictures()->create(['picture' => $fullUrl]);
            }
        }

        $rating->load('pictures');

        $ratedUser = User::findOrFail($userId);
        ActivityLogHelper::logUserRating($rater, $ratedUser, $rating);

        return $this->successResponse($rating, 'Rating submitted successfully');
    }

    /**
     * Get public ratings for a user.
     */
    public function getUserRatings($userId)
    {
        $user = User::findOrFail($userId);
        $ratings = $user->ratings()->with('rater')->get();
        $averageRating = $user->averageRating();

        $ratings->load('pictures');

        return $this->successResponse([
            'ratings' => $ratings,
            'average' => $averageRating
        ]);
    }
}
