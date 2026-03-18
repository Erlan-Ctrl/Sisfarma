<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\Ai\AiAssistantService;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function index(Request $request)
    {
        $conversationId = (int) $request->session()->get('ai_conversation_id', 0);
        $conversation = $conversationId > 0 ? AiConversation::query()->find($conversationId) : null;

        if (! $conversation) {
            $conversation = AiConversation::create([
                'title' => null,
            ]);
            $request->session()->put('ai_conversation_id', $conversation->getKey());
        }

        $messages = $conversation->messages()
            ->orderBy('id')
            ->get();

        return view('admin.assistant.index', [
            'conversation' => $conversation,
            'messages' => $messages,
            'aiProvider' => (string) config('ai.provider', 'local'),
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $conversationId = (int) $request->session()->get('ai_conversation_id', 0);
        $conversation = $conversationId > 0 ? AiConversation::query()->find($conversationId) : null;
        if (! $conversation) {
            $conversation = AiConversation::create(['title' => null]);
            $request->session()->put('ai_conversation_id', $conversation->getKey());
        }

        $userMessage = AiMessage::create([
            'ai_conversation_id' => $conversation->getKey(),
            'role' => 'user',
            'content' => trim($validated['message']),
            'meta' => null,
        ]);

        $assistant = app(AiAssistantService::class)->reply($conversation, $userMessage->content);

        $assistantMessage = AiMessage::create([
            'ai_conversation_id' => $conversation->getKey(),
            'role' => 'assistant',
            'content' => (string) ($assistant['content'] ?? ''),
            'meta' => (array) ($assistant['meta'] ?? []),
        ]);

        return response()->json([
            'ok' => true,
            'conversation_id' => $conversation->getKey(),
            'user_message' => [
                'id' => $userMessage->getKey(),
                'role' => $userMessage->role,
                'content' => $userMessage->content,
                'created_at' => optional($userMessage->created_at)->toISOString(),
            ],
            'assistant_message' => [
                'id' => $assistantMessage->getKey(),
                'role' => $assistantMessage->role,
                'content' => $assistantMessage->content,
                'created_at' => optional($assistantMessage->created_at)->toISOString(),
                'meta' => $assistantMessage->meta,
            ],
        ]);
    }

    public function reset(Request $request)
    {
        $request->session()->forget('ai_conversation_id');

        return redirect()
            ->route('admin.assistant')
            ->with('status', 'Conversa reiniciada.');
    }
}

