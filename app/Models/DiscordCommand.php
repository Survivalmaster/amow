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
        'handler_key',
        'access_mode',
        'role_id',
        'allow_any_channel',
        'command_options',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'allow_any_channel' => 'boolean',
            'command_options' => 'array',
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(DiscordWebhook::class, 'discord_webhook_id');
    }
}
