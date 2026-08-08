<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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

    public static function nextVersion(?string $latestVersion = null): string
    {
        $latestVersion ??= static::query()
            ->latest('released_at')
            ->latest()
            ->value('version');

        $version = Str::of((string) $latestVersion)->lower()->trim()->ltrim('v')->toString();

        if ($version === '' || ! preg_match('/^\d+(?:\.\d+)*$/', $version)) {
            return '0.0.1';
        }

        $parts = explode('.', $version);
        $lastIndex = count($parts) - 1;
        $parts[$lastIndex] = (string) (((int) $parts[$lastIndex]) + 1);

        return implode('.', $parts);
    }

    public static function latestDiscordChannelId(): ?string
    {
        return static::query()
            ->whereNotNull('discord_channel_id')
            ->latest('id')
            ->value('discord_channel_id');
    }
}
