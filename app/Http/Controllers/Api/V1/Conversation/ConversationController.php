<?php

namespace App\Http\Controllers\Api\V1\Conversation;

use App\Events\NewMessage;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\Api\V1\Conversation\ConversationService;
use App\Models\User;

class ConversationController extends Controller
{
    use ApiResponse;

    protected $conversationService;

    public function __construct(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;
    }

    /**
     * Create a conversation between users.
     * Expects a list of participant user IDs and an optional subject.
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'participant_ids' => 'required|array|min:2', // at least two participants
            'participant_ids.*' => 'exists:users,id',
            'subject'         => 'nullable|string|max:255',
        ]);

        $conversation = $this->conversationService->createConversation($validated['participant_ids'], $validated['subject'] ?? null);

        return $this->createdResponse($conversation->load('participants'), 'Conversation created successfully');
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $conversation = Conversation::findOrFail($conversationId);
        $sender = $request->user();

        // Only allow if sender is a participant (policy/middleware should enforce this as well)
        if (!$conversation->participants()->where('user_id', $sender->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied: not a participant',
            ], 403);
        }

        $message = $this->conversationService->sendMessage($conversation, $sender, $validated['content']);

        // Broadcast new message event (see event section below)
        broadcast(new NewMessage($message))->toOthers();

        return $this->successResponse($message, 'Message sent succesfully.');
    }

    /**
     * Fetch conversations for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $conversations = $this->conversationService->getConversations($user);

        return $this->successResponse($conversations, 'Conversation retrieved successfully.');
    }

    /**
     * Retrieve messages in a conversation with pagination.
     */
    public function getMessages(Request $request, $conversationId)
    {
        $perPage = $request->query('per_page', 10);
        $conversation = Conversation::findOrFail($conversationId);

        // Ensure the user is a participant.
        if (!$conversation->participants()->where('user_id', $request->user()->id)->exists()) {
            return $this->forbiddenResponse('Access denied: not a participant');
        }

        $messages = $this->conversationService->getMessages($conversation, $perPage);

        return $this->successResponse($messages, 'Messages retrieved successfully');
    }

    /**
     * Mark messages as read in a conversation.
     */
    public function markAsRead(Request $request, $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $user = $request->user();
        $this->conversationService->markMessagesAsRead($conversation, $user);

        return $this->successResponse(null, 'Messages marked as read');
    }

    /**
     * Soft delete a message in a conversation.
     */
    public function deleteMessage(Request $request, $conversationId, $messageId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $message = Message::findOrFail($messageId);
        $user = $request->user();

        try {
            $this->conversationService->deleteMessage($conversation, $message, $user);
        } catch (\Exception $e) {
            return $this->errorResponse("{$e->getMessage()}");
        }

        return $this->successResponse(null, 'Message deleted successfully');
    }

    /**
     * Search messages in a conversation.
     */
    public function searchMessages(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'query' => 'required|string',
        ]);

        $perPage = $request->query('per_page', 10);
        $conversation = Conversation::findOrFail($conversationId);

        if (!$conversation->participants()->where('user_id', $request->user()->id)->exists()) {
            return $this->forbiddenResponse('Access denied: not a participant');
        }

        $messages = $this->conversationService->searchMessages($conversation, $validated['query'], $perPage);

        return $this->successResponse($messages, 'Search results');
    }
}
