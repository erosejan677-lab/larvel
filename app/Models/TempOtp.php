<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempOtp extends Model
{
    use HasFactory;
    protected $fillable = ['phone','otp','expires_at'];
}
