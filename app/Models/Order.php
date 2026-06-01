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
        'expected_delivery_date'    => 'date',
        'actual_delivery_date'      => 'date',
        'subtotal'                  => 'decimal:2',
        'delivery_fee'              => 'decimal:2',
        'platform_fee'              => 'decimal:2',
        'total_amount'              => 'decimal:2',
        'is_guest_order'            => 'boolean', // ✅ cast for easy if($order->is_guest_order)
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * ✅ Safe buyer relationship — returns null for guest orders
     * instead of crashing when buyer_id is a UUID string
     */
    public function buyer()
    {
        // Guest orders store UUID in buyer_id — don't query users table
        if ($this->is_guest_order) {
            return $this->belongsTo(User::class, 'buyer_id')
                        ->whereRaw('1 = 0'); // always returns null, no DB query
        }

        return $this->belongsTo(User::class, 'buyer_id');
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
