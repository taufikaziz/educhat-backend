<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\RAGService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes
    public int $tries = 3;

    public function __construct(
        private Document $document
    ) {}

    public function handle(RAGService $ragService): void
    {
        @set_time_limit(300);

        Log::info('Processing document', [
            'document_id' => $this->document->id,
            'session_id' => $this->document->session_id
        ]);

        $disk = config('filesystems.default', 'local');

        if (! Storage::disk($disk)->exists($this->document->file_path)) {
            $this->document->update([
                'status' => 'failed',
                'error_message' => 'Document file not found in storage',
            ]);

            Log::error('Document file not found', [
                'document_id' => $this->document->id,
                'disk' => $disk,
                'path' => $this->document->file_path,
            ]);

            return;
        }

        $fileContent = Storage::disk($disk)->get($this->document->file_path);

        $result = $ragService->processDocument(
            $fileContent,
            $this->document->filename,
            $this->document->session_id
        );

        if ($result['success']) {
            $this->document->update([
                'status' => 'ready',
                'num_chunks' => $result['num_chunks'] ?? null,
            ]);

            Log::info('Document processed successfully', [
                'document_id' => $this->document->id,
                'num_chunks' => $result['num_chunks'] ?? 0
            ]);
        } else {
            $this->document->update([
                'status' => 'failed',
                'error_message' => $result['message'] ?? 'Unknown error',
            ]);

            Log::error('Document processing failed', [
                'document_id' => $this->document->id,
                'error' => $result['message'] ?? 'Unknown error'
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->document->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);

        Log::error('ProcessDocument job failed', [
            'document_id' => $this->document->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
