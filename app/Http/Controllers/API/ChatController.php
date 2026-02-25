<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\Document;
use App\Models\Message;
use App\Services\RAGService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        private RAGService $ragService
    ) {}

    private function successResponse(string $message, array $data = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function errorResponse(string $message, int $status = 400, array $data = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => empty($data) ? null : $data,
        ], $status);
    }

    public function createSession(Request $request): JsonResponse
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
        ]);

        $document = Document::where('id', $request->document_id)
            ->where('user_id', auth()->id())
            ->first();

        if (! $document) {
            return $this->errorResponse('Document not found', 404);
        }

        if (! $document->isReady()) {
            return $this->errorResponse('Document is not ready yet. Status: '.$document->status, 400);
        }

        $session = ChatSession::firstOrCreate(
            ['session_id' => $document->session_id],
            [
                'user_id' => auth()->id(),
                'document_id' => $document->id,
                'title' => 'Chat: '.$document->original_filename,
            ]
        );

        return $this->successResponse('Chat session created', [
            'session' => $session->load('document'),
        ], 201);
    }

    public function query(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'question' => 'required|string|max:1000',
        ]);

        $chatSession = null;
        $userMessage = null;

        if (auth()->check()) {
            $chatSession = ChatSession::where('session_id', $request->session_id)
                ->where('user_id', auth()->id())
                ->first();

            if (! $chatSession) {
                return $this->errorResponse('Session not found', 404);
            }

            $userMessage = Message::create([
                'chat_session_id' => $chatSession->id,
                'role' => 'user',
                'content' => $request->question,
            ]);
        } else {
            $document = Document::where('session_id', $request->session_id)
                ->whereNull('user_id')
                ->first();

            if (! $document || ! $document->isReady()) {
                return $this->errorResponse('Session not found', 404);
            }
        }

        $result = $this->ragService->query($request->question, $request->session_id);

        if (! $result['success']) {
            return $this->errorResponse($result['message'] ?? 'Failed to get answer', 400, [
                'provider_response' => $result,
            ]);
        }

        $assistantMessage = null;
        if ($chatSession) {
            $assistantMessage = Message::create([
                'chat_session_id' => $chatSession->id,
                'role' => 'assistant',
                'content' => $result['answer'],
            ]);

            $chatSession->touch();
        }

        return $this->successResponse('Query processed successfully', [
            'answer' => $result['answer'],
            'user_message' => $userMessage,
            'assistant_message' => $assistantMessage,
            'saved_history' => auth()->check(),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        if (! auth()->check()) {
            $document = Document::where('session_id', $request->session_id)
                ->whereNull('user_id')
                ->first();

            if (! $document || ! $document->isReady()) {
                return $this->errorResponse('Session not found', 404);
            }
        }

        $result = $this->ragService->generateSummary($request->session_id);

        if (! $result['success']) {
            return $this->errorResponse($result['message'] ?? 'Failed to generate summary', 400, [
                'provider_response' => $result,
            ]);
        }

        if (! auth()->check()) {
            return $this->successResponse('Summary generated successfully', [
                'summary' => $result['summary'],
                'summary_message' => null,
                'saved_history' => false,
            ]);
        }

        $chatSession = ChatSession::where('session_id', $request->session_id)
            ->where('user_id', auth()->id())
            ->first();

        if (! $chatSession) {
            return $this->errorResponse('Session not found', 404);
        }

        $message = Message::create([
            'chat_session_id' => $chatSession->id,
            'role' => 'system',
            'content' => "**Ringkasan Dokumen:**\n\n".$result['summary'],
        ]);

        return $this->successResponse('Summary generated successfully', [
            'summary' => $result['summary'],
            'summary_message' => $message,
            'saved_history' => true,
        ]);
    }

    public function messages(string $sessionId): JsonResponse
    {
        $chatSession = ChatSession::where('session_id', $sessionId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $chatSession) {
            return $this->errorResponse('Session not found', 404);
        }

        $messages = $chatSession->messages()
            ->orderBy('created_at')
            ->get();

        return $this->successResponse('Messages fetched', [
            'messages' => $messages,
        ]);
    }

    public function index(): JsonResponse
    {
        $sessions = ChatSession::where('user_id', auth()->id())
            ->with('document')
            ->orderBy('updated_at', 'desc')
            ->get();

        return $this->successResponse('Sessions fetched', [
            'sessions' => $sessions,
        ]);
    }

    public function destroy(string $sessionId): JsonResponse
    {
        $chatSession = ChatSession::where('session_id', $sessionId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $chatSession) {
            return $this->errorResponse('Session not found', 404);
        }

        $chatSession->delete();

        return $this->successResponse('Chat session deleted successfully');
    }
}
