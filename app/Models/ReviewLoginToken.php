<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewLoginToken extends Model
{
    protected $fillable = ['user_id','token','expires_at','used_at'];
    protected $casts = ['expires_at' => 'datetime', 'used_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isValid()
    {
        return is_null($this->used_at) && now()->lt($this->expires_at);
    }
}
