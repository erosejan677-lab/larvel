<?php
// app/Services/ConversationService.php

namespace App\Services\Api\V1\Conversation;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConversationService
{
    /**
     * Create a new conversation between users.
     * $participantIds should be an array of user IDs.
     */
    public function createConversation(array $participantIds, $subject = null)
    {
        return DB::transaction(function () use ($participantIds, $subject) {
            $conversation = Conversation::create(['subject' => $subject]);
            $conversation->participants()->attach($participantIds);
            return $conversation;
        });
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Conversation $conversation, User $sender, $content)
    {
        // Ensure the sender is a participant
        if (!$conversation->participants()->where('user_id', $sender->id)->exists()) {
            throw new \Exception('User not a participant in the conversation');
        }
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $sender->id,
            'content'         => $content,
        ]);

        // Reload sender relation if needed
        $message->load('sender');

        return $message;
    }

    /**
     * Fetch conversations for a user.
     */
    public function getConversations(User $user)
    {
        return $user->conversations()->with(['participants', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }])->orderBy('updated_at', 'desc')->get();
    }

    /**
     * Retrieve messages in a conversation with pagination.
     */
    public function getMessages(Conversation $conversation, $perPage = 10)
    {
        return Message::where('conversation_id', $conversation->id)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mark all messages in a conversation as read by a user.
     */
    public function markMessagesAsRead(Conversation $conversation, User $user)
    {
        // Update only messages not sent by the user and not already read.
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Soft delete a message.
     */
    public function deleteMessage(Conversation $conversation, Message $message, User $user)
    {
        // Only allow deletion if the user is the sender or has proper permissions.
        if ($message->sender_id !== $user->id) {
            throw new \Exception('Unauthorized');
        }
        $message->delete();
        return true;
    }

    /**
     * Search messages in a conversation.
     */
    public function searchMessages(Conversation $conversation, $query, $perPage = 10)
    {
        return Message::where('conversation_id', $conversation->id)
            ->where('content', 'LIKE', '%' . $query . '%')
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
