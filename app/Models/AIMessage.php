<?php

namespace App\Models;

use Database\Factories\AIMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIMessage extends Model
{
    /** @use HasFactory<AIMessageFactory> */
    use HasFactory;

    protected $table = 'ai_messages';

    protected $fillable = [
        'ai_conversation_id',
        'role',
        'content',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function aiConversation(): BelongsTo
    {
        return $this->belongsTo(AIConversation::class, 'ai_conversation_id');
    }
}
