<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscordCommand extends Model
{
    protected $fillable = [
        'discord_webhook_id',
        'name',
        'command_name',
        'command_description',
        'access_mode',
        'role_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(DiscordWebhook::class, 'discord_webhook_id');
    }
}
