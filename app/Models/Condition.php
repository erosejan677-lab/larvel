<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    use HasFactory;

    protected $table = 'conditions';

    protected $fillable = ['title', 'description'];


    protected $hidden = ['created_at', 'updated_at'];


    public function products() {
        $this->hasMany(Product::class);
    }
}
