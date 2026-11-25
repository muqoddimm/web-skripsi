<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatLog extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'user_message',
        'called_function',
        'function_payload_json',
        'bot_response',
        'is_out_of_scope',
        'category',
        'status',
        'admin_reply',
        'admin_id',
        'replied_at'
    ];

    protected $casts = [
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'replied_at'      => 'datetime',
        'is_out_of_scope' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
