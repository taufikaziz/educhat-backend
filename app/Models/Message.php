<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'chat_session_id',
        'role',
        'content',
    ];

    // Relationships
    public function chatSession()
    {
        return $this->belongsTo(ChatSession::class);
    }
}