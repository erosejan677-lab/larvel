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

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    public function preferences() {
        return $this->hasOne(UserPreference::class);
    }

    public function addresses() {
        return $this->hasMany(Address::class);
    }

    public function products() {
        return $this->hasMany(Product::class);
    }

    public function followers() {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id')->withTimestamps();
    }

    public function following() {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id')->withTimestamps();
    }

    public function likedProducts() {
        return $this->belongsToMany(Product::class, 'product_likes', 'user_id', 'product_id')->withTimestamps();
    }

    public function savedProducts() {
        return $this->belongsToMany(Product::class, 'product_saves', 'user_id', 'product_id')->withTimestamps();
    }

    public function ratings() {
        return $this->hasMany(Rating::class, 'user_id');
    }

    public function givenRatings() {
        return $this->hasMany(Rating::class, 'rater_id');
    }

    public function averageRating() {
        return $this->ratings()->avg('rating');
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user')->withTimestamps();
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function sentOffers()
    {
        return $this->hasMany(Offer::class, 'offerer_id');
    }

    public function bankDetail()
    {
        return $this->hasOne(BankDetail::class);
    }

    public function shop() {
        return $this->hasOne(Shop::class);
    }

    public function bankTransactions() {
        return $this->hasMany(BankTransaction::class);
    }
}
