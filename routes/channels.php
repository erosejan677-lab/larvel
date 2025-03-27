<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;
/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // Allow access if the user is a participant in the conversation.
    return Conversation::where('id', $conversationId)
        ->whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->exists();
});
