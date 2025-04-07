<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Conversation\ConversationController;

Route::prefix('messaging/conversations')->middleware(['auth:sanctum'])->group(function () {
    // Create a new conversation.
    Route::post('create', [ConversationController::class, 'create']);

    // Fetch conversations for the authenticated user.
    Route::get('show', [ConversationController::class, 'index']);

    // Send a message in a conversation.
    Route::post('{conversationId}/messages', [ConversationController::class, 'sendMessage']);

    // Retrieve messages in a conversation with pagination.
    Route::get('{conversationId}/messages', [ConversationController::class, 'getMessages']);

    // Mark messages as read.
    Route::post('{conversationId}/mark-read', [ConversationController::class, 'markAsRead']);

    // Soft delete a message.
    Route::delete('{conversationId}/messages/{messageId}', [ConversationController::class, 'deleteMessage']);

    // Search messages in a conversation.
    Route::get('{conversationId}/messages/search', [ConversationController::class, 'searchMessages']);
});
