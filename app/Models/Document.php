<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'original_filename',
        'file_path',
        'file_size',
        'num_pages',
        'num_chunks',
        'session_id',
        'status',
        'error_message',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'num_pages' => 'integer',
        'num_chunks' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chatSessions()
    {
        return $this->hasMany(ChatSession::class);
    }

    // Helper methods
    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}