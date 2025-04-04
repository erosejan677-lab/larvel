<?php

namespace App\Services\Api\V1\Listing;

use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OfferService
{
    /**
     * Create a new offer on a product.
     *
     * @param User $user  The authenticated user making the offer.
     * @param  array  $data  Validated data: product_id, offer_price, message.
     * @return Offer
     * @throws \Exception
     */
    public function createOffer($user, array $data)
    {
        $product = Product::findOrFail($data['product_id']);

        // Prevent self-offer.
        if ($product->user_id == $user->id) {
            throw new \Exception('You cannot make an offer on your own product');
        }

        // Validate that the offered price is less than the listed price.
        if ($data['offer_price'] >= $product->price) {
            throw new \Exception('Offer must be less than the product price');
        }

        // Ensure the offer is at least 10% of the product price.
        $minOffer = $product->price * 0.1;
        if ($data['offer_price'] < $minOffer) {
            throw new \Exception('Offer must be at least 10% of the product price');
        }

        // Create the offer record.
        $offer = Offer::create([
            'product_id'  => $product->id,
            'offerer_id'  => $user->id,
            'offer_price' => $data['offer_price'],
            'message'     => $data['message'] ?? null,
            'status'      => 'pending'
        ]);

        return $offer;
    }

    /**
     * Get offers received by the authenticated user (i.e. on products owned by the user).
     */
    public function getReceivedOffers($user)
    {
        return Offer::whereHas('product', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with('product', 'offerer')->get();
    }

    /**
     * Get offers sent by the authenticated user.
     */
    public function getSentOffers($user)
    {
        return Offer::where('offerer_id', $user->id)
            ->with('product')
            ->get();
    }

    /**
     * Accept an offer.
     * Either participant (offerer or product owner) may accept the current terms.
     *
     * @param User $user  The authenticated user.
     * @param  int  $offerId
     * @return Offer
     * @throws \Exception
     */
    public function acceptOffer($user, $offerId)
    {
        $offer = Offer::with('product')->findOrFail($offerId);

        // Ensure the user is a participant.
        if (!in_array($user->id, [$offer->offerer_id, $offer->product->user_id])) {
            throw new \Exception('Unauthorized: You are not a participant in this negotiation');
        }

        // Only allow accepting if the offer is in a negotiable state.
        if (!in_array($offer->status, ['pending', 'countered'])) {
            throw new \Exception('Offer cannot be accepted in its current state');
        }

        $offer->status = 'accepted';
        $offer->save();

        return $offer;
    }

    /**
     * Reject an offer.
     * Either participant may reject the negotiation.
     *
     * @param User $user
     * @param  int  $offerId
     * @return Offer
     * @throws \Exception
     */
    public function rejectOffer($user, $offerId)
    {
        $offer = Offer::with('product')->findOrFail($offerId);

        // Ensure the user is a participant.
        if (!in_array($user->id, [$offer->offerer_id, $offer->product->user_id])) {
            throw new \Exception('Unauthorized: You are not a participant in this negotiation');
        }

        if (!in_array($offer->status, ['pending', 'countered'])) {
            throw new \Exception('Offer cannot be rejected in its current state');
        }

        $offer->status = 'rejected';
        $offer->save();

        return $offer;
    }

    /**
     * Counter an offer.
     * Either participant may counter by proposing a new price.
     *
     * @param User $user  The authenticated user.
     * @param  int  $offerId
     * @param  array  $data  Validated data: counter_price, message.
     * @return Offer
     * @throws \Exception
     */
    public function counterOffer($user, $offerId, array $data)
    {
        $offer = Offer::with('product')->findOrFail($offerId);

        // Ensure the user is a participant.
        if (!in_array($user->id, [$offer->offerer_id, $offer->product->user_id])) {
            throw new \Exception('Unauthorized: You are not a participant in this negotiation');
        }

        // Validate the new counter offer price.
        if ($data['counter_price'] >= $offer->product->price) {
            throw new \Exception('Counter offer must be less than the product price');
        }
        $minOffer = $offer->product->price * 0.1;
        if ($data['counter_price'] < $minOffer) {
            throw new \Exception('Counter offer must be at least 10% of the product price');
        }

        // Update the current offer with the new counter price and message.
        $offer->offer_price = $data['counter_price'];
        $offer->status = 'countered';
        if (isset($data['message'])) {
            $offer->message = $data['message'];
        }
        $offer->save();

        return $offer;
    }
}
