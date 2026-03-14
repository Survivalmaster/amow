<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordSetting extends Model
{
    protected $fillable = [
        'wpnn_webhook_url',
        'wpnn_embed_color',
        'wpnn_footer_text',
        'wpnn_enabled',
    ];

    protected function casts(): array
    {
        return [
            'wpnn_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'wpnn_embed_color' => '#c65b3f',
            'wpnn_footer_text' => 'WPNN desk',
            'wpnn_enabled' => false,
        ]);
    }
}
