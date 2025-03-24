<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';


    protected $fillable = ['reviewee_id', 'reviewer_id', 'review'];

    /**
     * The user who is being reviewed.
     */
    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    /**
     * The user who left the review.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
