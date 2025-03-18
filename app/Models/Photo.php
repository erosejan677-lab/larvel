<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Photo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'photos';

    protected $fillable = ['product_id', 'image_path'];

    public function product() {
        return $this->belongsTo(Product::class);
    }

}
