<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RAGService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.rag.url', 'http://127.0.0.1:5050'), '/');
    }

    public function processDocument(string $fileContent, string $filename, string $sessionId): array
    {
        try {
            $response = Http::timeout(300)->attach(
                'file',
                $fileContent,
                $filename
            )->post("{$this->baseUrl}/process", [
                'session_id' => $sessionId,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('RAG Service Error', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process document'
            ];

        } catch (\Exception $e) {
            Log::error('RAG Service Exception', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function query(string $question, string $sessionId): array
    {
        try {
            $response = Http::timeout(60)->post("{$this->baseUrl}/query", [
                'question' => $question,
                'session_id' => $sessionId,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('RAG Query Error', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to get answer'
            ];

        } catch (\Exception $e) {
            Log::error('RAG Query Exception', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function generateSummary(string $sessionId): array
    {
        try {
            $response = Http::timeout(60)->post("{$this->baseUrl}/summary", [
                'session_id' => $sessionId,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('RAG Summary Error', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to generate summary'
            ];

        } catch (\Exception $e) {
            Log::error('RAG Summary Exception', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function healthCheck(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
