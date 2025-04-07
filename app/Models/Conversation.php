<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $table = 'conversations';

    protected $fillable = ['subject'];

    /**
     * The users participating in the conversation.
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_user')->withTimestamps();
    }

    /**
     * The messages in this conversation.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
