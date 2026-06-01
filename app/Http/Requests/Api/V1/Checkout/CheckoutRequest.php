<?php

namespace App\Http\Requests\Api\V1\Checkout;

use App\Models\Cart;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        // ✅ Cast user ID to int — matches bigint column
        $userId = (int) $this->user()->id;
        $cartId = optional(Cart::firstOrCreate(['user_id' => $userId]))->id;

        return [
            'seller_id' => [
                'required',
                'exists:users,id',
                function ($attr, $value, $fail) {
                    if ((int)$value === (int)$this->user()->id) {
                        $fail('You cannot checkout your own products.');
                    }
                }
            ],
            'cart_items'              => ['required', 'array', 'min:1'],
            'cart_items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
                Rule::exists('cart_items', 'product_id')
                    ->where('cart_id', $cartId),
            ],
            'cart_items.*.quantity'   => ['required', 'integer', 'min:1'],
            'delivery_address_id'     => [
                'required',
                // ✅ Cast user_id to int in the where clause
                Rule::exists('addresses', 'id')->where(function ($query) use ($userId) {
                    $query->where('address_type', 'shipping')
                          ->where('user_id', (string) $userId); // ✅ addresses.user_id is TEXT (supports UUIDs too)
                }),
            ],
            'cart_items.*.offer_id'   => 'nullable|string|exists:offers,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $sellerId  = (int) $this->input('seller_id'); // ✅ cast to int
            $cartItems = $this->input('cart_items', []);

            foreach ($cartItems as $i => $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    $validator->errors()->add(
                        "cart_items.{$i}.product_id",
                        "Product ID {$item['product_id']} not found."
                    );
                    continue;
                }

                // ✅ Both cast to int for safe comparison
                if ((int)$product->user_id !== $sellerId) {
                    $validator->errors()->add(
                        "cart_items.{$i}.product_id",
                        "Product ID {$product->id} does not belong to seller {$sellerId}."
                    );
                    continue;
                }

                if ($item['quantity'] > $product->quantity_left) {
                    $validator->errors()->add(
                        "cart_items.{$i}.quantity",
                        "Only {$product->quantity_left} unit(s) left in stock for product ID {$product->id}."
                    );
                }

                if ($product->price * $item['quantity'] > 1000000) {
                    $validator->errors()->add(
                        "cart_items.{$i}.quantity",
                        "Line total for product ID {$product->id} exceeds allowed maximum."
                    );
                }

                if (isset($item['offer_id']) && $item['offer_id'] !== null) {
                    $offer = Offer::where('id', $item['offer_id'])
                        ->where('product_id', $item['product_id'])
                        ->where('seller_id', $sellerId) // ✅ int
                        ->first();

                    if (!$offer) {
                        $validator->errors()->add(
                            "cart_items.{$i}.offer_id",
                            "Offer ID {$item['offer_id']} is invalid or inactive for product ID {$item['product_id']}."
                        );
                    }
                }
            }
        });
    }

    public function messages()
    {
        return [
            'cart_items.*.product_id.exists' => 'The product must exist in your cart.',
            'cart_items.*.quantity.min'      => 'You must request at least 1 unit.',
            'delivery_address_id.required'   => 'Please add an address to confirm order.',
            'delivery_address_id.exists'     => 'The selected delivery address is invalid or does not belong to you.',
        ];
    }
}
