<?php

namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Checkout\CheckoutRequest;
use App\Http\Requests\Api\V1\Checkout\CheckoutRequestGuest;
use App\Models\Order;
use App\Services\Api\V1\Cart\CheckoutService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * Process a checkout for items from a given seller.
     */
    public function checkout(CheckoutRequest $request)
    {
        $validated = $request->validated();

    // Cast seller_id to string
    $sellerId = (string) $validated['seller_id'];

        try {
            $order = $this->checkoutService->processCheckout(
                $request->user(),
            $sellerId,  // ← Changed from $validated['seller_id'] to $sellerId
                $validated['cart_items'],
                $validated['delivery_address_id']
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Checkout completed successfully',
                'data'    => $order
            ], 201);

        } catch (\Exception $e) {
            // Service‐level errors (should be rare)
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
 public function checkoutGuest(CheckoutRequestGuest $request)
{
    $v = $request->validated();
    $guestId = $v['guest_id'];
    $items = $v['cart_items'];
    $info = $v['guest_info'];
    
    // Get the correct seller_id from the product (ignore what Flutter sent)
    $productId = $items[0]['product_id'];
    $product = \App\Models\Product::find($productId);
    
    if (!$product) {
        return response()->json([
            'status' => 'error',
            'message' => 'Product not found'
        ], 422);
    }
    
    // Use the product's user_id as seller_id
    $sellerId = (string) $product->user_id;
    
    logger('checkoutGuest payload', [
        'flutter_sent_seller_id' => $v['seller_id'] ?? 'not set',
        'using_seller_id' => $sellerId,
        'product_id' => $productId,
        'product_user_id' => $product->user_id,
        'guest_id' => $guestId,
        'cart_items' => $items,
        'guest_info' => $info,
    ]);
    
    try {
        $order = $this->checkoutService
            ->processCheckoutGuest($guestId, $sellerId, $items, $info);

        return response()->json([
            'status' => 'success',
            'message' => 'Checkout completed successfully',
            'data' => $order,
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 422);
    }
}

    /**
     * Retrieve orders for the authenticated user.
     * either sold or purchased
     */
    public function getOrders(Request $request)
    {
        logger('guest: ', $request->all());

       
    $type = $request->query('type', 'sold'); // default is 'sold'
    $userId = (string) $request->user()->id;  // ← CAST TO STRING HERE

        if ($type === 'purchased') {
            $orders = Order::where('buyer_id', $userId)
                ->with(['items.product', 'items.product.photos', 'seller', 'deliveryAddress'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else { // default or 'sold'.
            $orders = Order::where('seller_id', $userId)
                ->with(['items.product', 'items.product.photos', 'buyer', 'deliveryAddress'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Orders retrieved successfully',
            'data'    => $orders
        ], 200);
    }

    /**
     * Retrieve a specific order's details.
     */
    public function getOrder($orderId, Request $request)
    {

    $userId = (string) $request->user()->id;  // ← CAST TO STRING HERE
    $order = Order::where('buyer_id', $userId)  // ← Use $userId here, not $request->user()->id
            ->with('items.product', 'seller')
            ->findOrFail($orderId);

        return response()->json([
            'status'  => 'success',
            'message' => 'Order details retrieved successfully',
            'data'    => $order
        ], 200);
    }
    public function getOrderForGuest(Request $request)
    {
        $orderId = $request->orderId;
        $guestId = $request->guest_id;

        // Get the order for this guest
        $order = Order::where('id', $orderId)
            ->where('buyer_id', $guestId)
            ->with('items.product', 'seller', 'items.product.photos')
            ->firstOrFail();

        logger('getOrderForGuest payload', [$order, $guestId]);

        // Fetch the address directly by delivery_address_id and user_id = guest_id (UUID)
        $address = \DB::table('addresses')
            ->where('id', $order->delivery_address_id)
            ->where('user_id', $guestId) // guestId is UUID for guest addresses
            ->first();


        // Convert to array and attach
        $orderData = $order->toArray();
        $orderData['delivery_address'] = $address;

        return response()->json([
            'status'  => 'success',
            'message' => 'Order details retrieved successfully',
            'data'    => $orderData
        ], 200);
    }

}
