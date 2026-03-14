<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordWebhook extends Model
{
    protected $fillable = [
        'name',
        'command_name',
        'command_description',
        'channel_id',
        'webhook_url',
        'embed_color',
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
}
