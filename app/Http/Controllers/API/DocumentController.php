<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Jobs\ProcessDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    private function successResponse(string $message, array $data = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $status);
    }

    public function upload(Request $request): JsonResponse
    {
        try {
            if (config('queue.default') === 'sync') {
                @set_time_limit(300);
            }

            $request->validate([
                'file' => 'required|file|mimes:pdf|max:10240', // max 10MB
            ]);

            $file = $request->file('file');
            $sessionId = Str::uuid()->toString();
            $disk = config('filesystems.default', 'local');

            // Save file
            $filename = $sessionId . '.pdf';
            $path = $file->storeAs('documents', $filename, $disk);

            // Create document record
            $document = Document::create([
                'user_id' => auth()->id(),
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'session_id' => $sessionId,
                'status' => 'processing',
            ]);

            // Dispatch job to process document
            ProcessDocument::dispatch($document);

            return $this->successResponse('Document uploaded and processing started', [
                'session_id' => $sessionId,
                'document_id' => $document->id,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Document upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Upload failed: '.$e->getMessage(), 500);
        }
    }

    public function status(string $sessionId): JsonResponse
    {
        $document = Document::where('session_id', $sessionId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$document) {
            return $this->errorResponse('Document not found', 404);
        }

        return $this->successResponse('Document status fetched', [
            'status' => $document->status,
            'num_chunks' => $document->num_chunks,
            'error_message' => $document->error_message,
            'document' => $document,
        ]);
    }

    public function index(): JsonResponse
    {
        $documents = Document::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse('Documents fetched', [
            'documents' => $documents,
        ]);
    }

    public function destroy(string $sessionId): JsonResponse
    {
        $document = Document::where('session_id', $sessionId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$document) {
            return $this->errorResponse('Document not found', 404);
        }

        // Delete file
        Storage::disk(config('filesystems.default', 'local'))->delete($document->file_path);

        // Delete record
        $document->delete();

        return $this->successResponse('Document deleted successfully');
    }

    public function file(string $sessionId)
    {
        $document = Document::where('session_id', $sessionId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $document) {
            return $this->errorResponse('Document not found', 404);
        }

        $disk = config('filesystems.default', 'local');

        if (! Storage::disk($disk)->exists($document->file_path)) {
            return $this->errorResponse('Document file not found', 404);
        }

        $safeFilename = str_replace('"', '', $document->original_filename);

        if ($disk === 'local') {
            $absolutePath = Storage::disk('local')->path($document->file_path);

            return response()->file($absolutePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$safeFilename.'"',
            ]);
        }

        $content = Storage::disk($disk)->get($document->file_path);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$safeFilename.'"',
        ]);
    }
}
