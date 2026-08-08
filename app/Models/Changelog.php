<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Changelog extends Model
{
    protected $fillable = [
        'discord_webhook_id',
        'discord_channel_id',
        'version',
        'title',
        'summary',
        'features',
        'added_features',
        'edited_features',
        'removed_features',
        'body',
        'status',
        'released_at',
        'discord_message_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'added_features' => 'array',
            'edited_features' => 'array',
            'removed_features' => 'array',
            'released_at' => 'datetime',
            'discord_message_sent_at' => 'datetime',
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(DiscordWebhook::class, 'discord_webhook_id');
    }

    public function scopeReleased(Builder $query): Builder
    {
        return $query->where('status', 'released')->whereNotNull('released_at');
    }

    public function isReleased(): bool
    {
        return $this->status === 'released' && $this->released_at !== null;
    }

    public function groupedFeatures(): array
    {
        return [
            'Added' => $this->added_features ?: $this->features ?: [],
            'Edited' => $this->edited_features ?: [],
            'Removed' => $this->removed_features ?: [],
        ];
    }
}
