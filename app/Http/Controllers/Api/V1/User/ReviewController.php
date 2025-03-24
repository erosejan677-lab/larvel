<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    /**
     * Create or update a text review for a user.
     *
     * @param Request $request
     * @param  int  $userId  The ID of the user being reviewed.
     * @return JsonResponse
     */
    public function store(Request $request, $userId)
    {
        // Validate that a review text is provided.
        $validated = $request->validate([
            'review' => 'required|string'
        ]);

        $reviewerId = auth()->id();
        if ($reviewerId == $userId) {
            return $this->errorResponse('You cannot give a review to yourself');
        }

        // Create or update the review (ensuring one review per reviewer per reviewee).
        $userReview = Review::updateOrCreate(
            ['reviewee_id' => $userId, 'reviewer_id' => $reviewerId],
            $validated
        );

        return $this->successResponse($userReview, 'Review submitted successfully');
    }

    /**
     * Update an existing text review.
     *
     * @param Request $request
     * @param  int  $userId  The reviewee's ID.
     * @return JsonResponse
     */
    public function update(Request $request, $userId)
    {
        $validated = $request->validate([
            'review' => 'required|string'
        ]);

        $reviewerId = auth()->id();
        $userReview = Review::where('reviewee_id', $userId)
            ->where('reviewer_id', $reviewerId)
            ->first();

        if (!$userReview) {
            return $this->noContentResponse();
        }

        $userReview->update($validated);

        return $this->successResponse($userReview, 'Review updated successfully');
    }

    /**
     * Delete a text review.
     *
     * @param Request $request
     * @param  int  $userId  The reviewee's ID.
     * @return JsonResponse
     */
    public function destroy(Request $request, $userId)
    {
        $reviewerId = auth()->id();
        $userReview = Review::where('reviewee_id', $userId)
            ->where('reviewer_id', $reviewerId)
            ->first();

        if (!$userReview) {
            return $this->noContentResponse();
        }

        $userReview->delete();

        return $this->successResponse(null, 'Review deleted successfully');
    }

    /**
     * Retrieve all text reviews for a given user.
     *
     * @param  int  $userId  The reviewee's ID.
     * @return JsonResponse
     */
    public function getReviews($userId)
    {
        $user = User::findOrFail($userId);
        $reviews = Review::with('reviewer')
            ->where('reviewee_id', $user->id)
            ->get();

        return $this->successResponse($reviews);
    }
}
