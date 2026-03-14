<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscordWebhook extends Model
{
    protected $fillable = [
        'name',
        'channel_id',
        'webhook_url',
        'embed_color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function commands(): HasMany
    {
        return $this->hasMany(DiscordCommand::class);
    }
}
