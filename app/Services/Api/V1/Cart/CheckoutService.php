<?php

namespace App\Services\Api\V1\Cart;

use App\Helpers\ActivityLogHelper;
use App\Mail\OrderSummaryToAdmin;
use App\Mail\OrderSummaryToSeller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Fees;
use App\Models\GuestCart;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutService
{
    protected $postexService;
    protected $blueExService; // ✅ was missing the property declaration

    public function __construct(PostexService $postexService, BlueExService $blueExService)
    {
        $this->postexService = $postexService;
        $this->blueExService = $blueExService;
    }

    // ✅ Changed: string $sellerId → int $sellerId
    public function processCheckout(User $buyer, int $sellerId, array $cartItems, int $deliveryAddressId): Order
    {
        return DB::transaction(function () use ($buyer, $sellerId, $cartItems, $deliveryAddressId) {

            $productIds = collect($cartItems)->pluck('product_id')->all();
            $products = Product::whereIn('id', $productIds)
                ->where('user_id', $sellerId) // ✅ now int = bigint, no type mismatch
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($productIds)) {
                throw new \Exception("One or more products were not found for this seller.");
            }

            $now      = now();
            $subtotal = 0;
            $itemsData = [];
            $offerId  = null; // ✅ initialize here to avoid undefined variable in Order::create

            foreach ($cartItems as $item) {
                $product = $products[$item['product_id']];
                $offerId = isset($item['offer_id']) ? (int) $item['offer_id'] : null;

                if ($item['quantity'] > $product->quantity_left) {
                    throw new \Exception("Insufficient stock for product ID {$product->id}.");
                }

                $offer = null;
                if ($offerId) {
                    $offer = Offer::where('id', $offerId)
                        ->where('product_id', $item['product_id'])
                        ->where('seller_id', $sellerId)
                        ->first();

                    if (!$offer) {
                        throw new \Exception("Invalid or inactive offer for product ID {$item['product_id']}.");
                    }
                }

                $price     = $offer ? $offer->price : $product->price;
                $lineTotal = $price * $item['quantity'];
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $price,
                    'total'      => $lineTotal,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $fees            = Fees::whereIn('fee_type', ['delivery', 'platform', 'market_threshold'])
                ->pluck('fee_amount', 'fee_type');
            $deliveryFee     = $fees['delivery'] ?? 0;
            $platformFeeRate = $fees['platform'] ?? 0;

            $buyerTotal        = $subtotal + $deliveryFee;
            $platformFeeAmount = round($subtotal * $platformFeeRate, 2);
            $sellerPayout      = round($subtotal - $platformFeeAmount, 2);

            $order = Order::create([
                'buyer_id'                  => $buyer->id,  // ✅ int from User model
                'seller_id'                 => $sellerId,   // ✅ int
                'subtotal'                  => $subtotal,
                'delivery_fee'              => $deliveryFee,
                'platform_fee'              => $platformFeeAmount,
                'total_amount'              => $buyerTotal,
                'expected_delivery_date'    => Carbon::now()->addDays(5),
                'tracking_no'               => 'CLSY-' . strtoupper(Str::random(10)),
                'actual_delivery_date'      => null,
                'status'                    => 'pending',
                'delivery_address_id'       => $deliveryAddressId,
                'total_seller_payout'       => $sellerPayout,
                'market_threshold_applied'  => 0,
                'offer_id'                  => $offerId ?? null,
            ]);

            $order->items()->createMany($itemsData);
            ActivityLogHelper::logOrderPlaced($order);

            try {
                Mail::to($order->seller->email)->send(new OrderSummaryToSeller($order));
                Mail::to(config('app.admin_email'))->send(new OrderSummaryToAdmin($order));
            } catch (\Exception $e) {
                Log::error('Failed to send order summary email: ' . $e->getMessage());
            }

            foreach ($cartItems as $item) {
                $p      = $products[$item['product_id']];
                $newLeft = $p->quantity_left - $item['quantity'];
                $p->update(['quantity_left' => $newLeft, 'sold' => $newLeft === 0]);
            }

            $this->sendShippingAndSms($order, $itemsData, $products, $buyerTotal);

            $cart          = Cart::firstOrCreate(['user_id' => $buyer->id]);
            $cartItemsById = $cart->items()
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');

            foreach ($cartItems as $item) {
                $ci        = $cartItemsById[$item['product_id']];
                $remaining = $ci->quantity - $item['quantity'];
                $remaining > 0 ? $ci->update(['quantity' => $remaining]) : $ci->delete();
            }

            return $order->load('items');
        });
    }

    // ✅ Changed: string $sellerId → int $sellerId
    public function processCheckoutGuest(
        string $guestId,   // ✅ UUID — keep as string, stored separately
        int    $sellerId,  // ✅ was string, now int to match bigint column
        array  $cartItems,
        array  $guestInfo
    ): Order {
        DB::reconnect();

        return DB::transaction(function () use ($guestId, $sellerId, $cartItems, $guestInfo) {

            // ✅ Guest address: user_id stored as TEXT for guests (UUID)
            // addresses.user_id must support both int (real users) and UUID (guests)
            // Make sure your addresses table has user_id as TEXT/VARCHAR — check below
            $address = Address::create([
                'user_id'                  => $guestId, // UUID string for guests
                'address_line_1'           => $guestInfo['address'],
                'address_line_2'           => $guestInfo['address_line_2'] ?? null,
                'city'                     => $guestInfo['city'],
                'state_province_or_region' => $guestInfo['state_province_or_region'] ?? null,
                'zip_or_postal_code'       => '00000',
                'address_type'             => 'shipping',
                'is_guest_address'         => 1,
            ]);

            $deliveryAddressId = $address->id;

            $productIds = collect($cartItems)->pluck('product_id')->all();
            $products   = Product::whereIn('id', $productIds)
                ->where('user_id', $sellerId) // ✅ int = bigint
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($productIds)) {
                throw new \Exception("One or more products were not found for this seller.");
            }

            $now       = now();
            $subtotal  = 0;
            $itemsData = [];

            foreach ($cartItems as $item) {
                $p = $products[$item['product_id']];
                if ($item['quantity'] > $p->quantity_left) {
                    throw new \Exception("Insufficient stock for product ID {$p->id}.");
                }

                $lineTotal = $p->price * $item['quantity'];
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'product_id' => $p->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $p->price,
                    'total'      => $lineTotal,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $fees            = Fees::whereIn('fee_type', ['delivery', 'platform', 'market_threshold'])
                ->pluck('fee_amount', 'fee_type');
            $deliveryFee     = $fees['delivery'] ?? 0;
            $platformFeeRate = $fees['platform'] ?? 0;

            $buyerTotal        = $subtotal + $deliveryFee;
            $platformFeeAmount = round($subtotal * $platformFeeRate, 2);
            $sellerPayout      = round($subtotal - $platformFeeAmount, 2);

            $order = Order::create([
                'buyer_id'                 => $guestId,   // ✅ UUID string for guests
                'guest_name'               => $guestInfo['first_name'] . ' ' . $guestInfo['last_name'],
                'guest_phone'              => $guestInfo['phone'],
                'guest_email'              => $guestInfo['email'],
                'is_guest_order'           => 1,
                'seller_id'                => $sellerId,  // ✅ int
                'subtotal'                 => $subtotal,
                'delivery_fee'             => $deliveryFee,
                'platform_fee'             => $platformFeeAmount,
                'total_amount'             => $buyerTotal,
                'expected_delivery_date'   => Carbon::now()->addDays(5),
                'tracking_no'              => 'CLSY-' . strtoupper(Str::random(10)),
                'status'                   => 'pending',
                'delivery_address_id'      => $deliveryAddressId,
                'total_seller_payout'      => $sellerPayout,
                'market_threshold_applied' => 0,
            ]);

            $order->items()->createMany($itemsData);

            try {
                Mail::to($order->seller->email)->send(new OrderSummaryToSeller($order));
                Mail::to(config('app.admin_email'))->send(new OrderSummaryToAdmin($order));
            } catch (\Exception $e) {
                Log::error('Failed order emails: ' . $e->getMessage());
            }

            foreach ($cartItems as $item) {
                $p       = $products[$item['product_id']];
                $newLeft = $p->quantity_left - $item['quantity'];
                $p->update(['quantity_left' => $newLeft, 'sold' => $newLeft === 0]);
            }

            $this->sendShippingAndSms($order, $itemsData, $products, $buyerTotal);

            $cart      = GuestCart::firstOrCreate(['guest_id' => $guestId]);
            $byProduct = $cart->items()
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');

            foreach ($cartItems as $item) {
                $ci     = $byProduct[$item['product_id']];
                $remain = $ci->quantity - $item['quantity'];
                $remain > 0 ? $ci->update(['quantity' => $remain]) : $ci->delete();
            }

            return $order;
        });
    }

    // ✅ Extracted repeated shipping+SMS logic into one private method
    private function sendShippingAndSms(Order $order, array $itemsData, $products, float $buyerTotal): void
    {
        $postexResponse = $this->postexService->sendOrderToPostex($order, $itemsData, $products, $buyerTotal);
        $blueEXResponse = $this->blueExService->sendOrderToBlueEx($order, $itemsData, $products, $buyerTotal);

        $trackingNumber = 'N/A';
        if (is_array($blueEXResponse) && isset($blueEXResponse['cnno'])) {
            $trackingNumber = $blueEXResponse['cnno'];
        } elseif (is_array($postexResponse) && isset($postexResponse['dist']['trackingNumber'])) {
            $trackingNumber = $postexResponse['dist']['trackingNumber'];
        }

        // Buyer SMS
        try {
            Http::asForm()->post('https://sendpk.com/api/sms.php', [
                'api_key'     => config('services.sendpk.api_key'),
                'sender'      => 'Closyyyy',
                'mobile'      => $order->guest_phone ?? $order->buyer->phone ?? '',
                'template_id' => 10119,
                'message'     => json_encode(['name' => $order->guest_name ?? $order->buyer->first_name ?? '', 'trackingID' => $trackingNumber]),
                'format'      => 'json',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed buyer SMS: ' . $e->getMessage());
        }

        // Seller SMS
        try {
            Http::asForm()->post('https://sendpk.com/api/sms.php', [
                'api_key'     => config('services.sendpk.api_key'),
                'sender'      => 'Closyyyy',
                'mobile'      => $order->seller->phone ?? '',
                'template_id' => 10120,
                'message'     => json_encode(['name' => $order->seller->first_name ?? '']),
                'format'      => 'json',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed seller SMS: ' . $e->getMessage());
        }
    }
}
