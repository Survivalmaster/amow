<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectChatMessage extends Model
{
    protected $fillable = [
        'sender_character_id',
        'recipient_character_id',
        'message',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'sender_character_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'recipient_character_id');
    }
}
