<?php

namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Checkout\CheckoutRequest;
use App\Http\Requests\Api\V1\Checkout\CheckoutRequestGuest;
use App\Models\Order;
use App\Models\Product;
use App\Services\Api\V1\Cart\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    public function checkout(CheckoutRequest $request)
    {
        $validated = $request->validated();

        // ✅ Cast to INT (not string) — columns are now bigint
        $sellerId = (int) $validated['seller_id'];

        try {
            $order = $this->checkoutService->processCheckout(
                $request->user(),
                $sellerId,
                $validated['cart_items'],
                $validated['delivery_address_id']
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Checkout completed successfully',
                'data'    => $order
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function checkoutGuest(CheckoutRequestGuest $request)
    {
        $v       = $request->validated();
        $guestId = $v['guest_id'];
        $items   = $v['cart_items'];
        $info    = $v['guest_info'];

        $productId = $items[0]['product_id'];
        $product   = Product::find($productId);

        if (!$product) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Product not found'
            ], 422);
        }

        // ✅ Cast to INT (not string)
        $sellerId = (int) $product->user_id;

        logger('checkoutGuest payload', [
            'flutter_sent_seller_id' => $v['seller_id'] ?? 'not set',
            'using_seller_id'        => $sellerId,
            'product_id'             => $productId,
            'product_user_id'        => $product->user_id,
            'guest_id'               => $guestId,
            'cart_items'             => $items,
            'guest_info'             => $info,
        ]);

        try {
            $order = $this->checkoutService
                ->processCheckoutGuest($guestId, $sellerId, $items, $info);

            return response()->json([
                'status'  => 'success',
                'message' => 'Checkout completed successfully',
                'data'    => $order,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function getOrders(Request $request)
    {
        $type   = $request->query('type', 'sold');
        
        // ✅ Cast to INT (not string)
        $userId = (int) $request->user()->id;

        if ($type === 'purchased') {
            $orders = Order::where('buyer_id', $userId)
                ->with(['items.product', 'items.product.photos', 'seller', 'deliveryAddress'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
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

    public function getOrder($orderId, Request $request)
    {
        // ✅ Cast to INT (not string)
        $userId = (int) $request->user()->id;

        $order = Order::where('buyer_id', $userId)
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
        $guestId = $request->guest_id; // UUID string — keep as string, this is correct

        $order = Order::where('id', $orderId)
            ->where('buyer_id', $guestId)
            ->with('items.product', 'seller', 'items.product.photos')
            ->firstOrFail();

        logger('getOrderForGuest payload', [$order, $guestId]);

        $address = DB::table('addresses')
            ->where('id', $order->delivery_address_id)
            ->where('user_id', $guestId)
            ->first();

        $orderData = $order->toArray();
        $orderData['delivery_address'] = $address;

        return response()->json([
            'status'  => 'success',
            'message' => 'Order details retrieved successfully',
            'data'    => $orderData
        ], 200);
    }
}
