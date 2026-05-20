<?php

namespace App\Models;

use App\Notifications\Api\V1\Auth\VerifyEmail;
use Bavix\Wallet\Interfaces\Confirmable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\CanConfirm;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements Wallet, Confirmable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, HasWallet, CanConfirm;

    protected $fillable = [
        'email',
        'password',
        'phone',
        'role',
        'first_name',
        'last_name',
        'location',
        'username',
        'profile_picture',
        'google_id',
        'phone'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Handle ID conversion for both MySQL and PostgreSQL
    public function getIdAttribute($value)
    {
        return is_numeric($value) ? (int) $value : $value;
    }

    public function setIdAttribute($value)
    {
        $this->attributes['id'] = is_numeric($value) ? (int) $value : (string) $value;
    }

    public function getKey()
    {
        $value = parent::getKey();
        return is_numeric($value) ? (int) $value : $value;
    }

    public function getKeyType()
    {
        return 'string';
    }

    public function getIncrementing()
    {
        return false;
    }

    // ADD THIS METHOD HERE
    protected static function booted()
    {
        static::created(function ($user) {
            // Only assign role if user has an ID
            if ($user->id) {
                // Check if role exists before assigning
                if (\Spatie\Permission\Models\Role::where('name', 'user')->exists()) {
                    $user->assignRole('user');
                }
            }
        });
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    // ... rest of your methods remain the same
}
