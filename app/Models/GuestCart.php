<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestCart extends Model
{
    use HasFactory;

    protected $fillable = ['guest_id', 'items'];

    protected $casts = [
        'items' => 'array',  // ADD THIS - auto handle JSON
        'guest_id' => 'string',  // ADD THIS
    ];

    public function items()
    {
        return $this->hasMany(GuestCartItem::class);
    }
}
