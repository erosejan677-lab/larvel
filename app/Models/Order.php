<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'guest_name',
        'guest_phone',
        'guest_email',
        'is_guest_order',
        'seller_id',
        'subtotal',
        'delivery_fee',
        'total_amount',
        'status',
        'delivery_address_id',
        'platform_fee',
        'expected_delivery_date',
        'actual_delivery_date',
        'tracking_no',
        'postex_tracking_no',
        'blueex_tracking_no',
        'total_seller_payout',
        'market_threshold_applied',
        'asked_for_review',
        'offer_id',
    ];

    protected $casts = [
        'expected_delivery_date'   => 'date',
        'actual_delivery_date'     => 'date',
        'subtotal'                 => 'decimal:2',
        'delivery_fee'             => 'decimal:2',
        'platform_fee'             => 'decimal:2',
        'total_amount'             => 'decimal:2',
        'is_guest_order'           => 'boolean', // ✅ makes if($order->is_guest_order) work cleanly
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * ✅ Keep this as the Eloquent relationship definition
     * (used internally by getBuyerAttribute)
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * ✅ This accessor intercepts $order->buyer BEFORE any SQL runs.
     * For guest orders, buyer_id is a UUID — never query users table.
     * For real users, loads normally via the relationship.
     */
    public function getBuyerAttribute()
    {
        // Guest order OR buyer_id is not a numeric integer → skip DB query
        if ($this->is_guest_order || !is_numeric($this->buyer_id)) {
            return null;
        }

        // Use Laravel's relation cache to avoid duplicate queries
        if (!array_key_exists('buyer', $this->relations)) {
            $this->load('buyer');
        }

        return $this->relations['buyer'] ?? null;
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function deliveryAddress()
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }

    public function bank_transaction()
    {
        return $this->hasOne(BankTransaction::class);
    }
}
