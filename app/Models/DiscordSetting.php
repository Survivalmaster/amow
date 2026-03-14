<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordSetting extends Model
{
    protected $fillable = [
        'wpnn_webhook_url',
        'wpnn_message_prefix',
        'wpnn_author_name',
        'wpnn_author_icon_url',
        'wpnn_thumbnail_url',
        'wpnn_embed_color',
        'wpnn_footer_text',
        'wpnn_show_timestamp',
        'wpnn_enabled',
    ];

    protected function casts(): array
    {
        return [
            'wpnn_enabled' => 'boolean',
            'wpnn_show_timestamp' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'wpnn_message_prefix' => null,
            'wpnn_author_name' => 'World Plastica News Network',
            'wpnn_author_icon_url' => null,
            'wpnn_thumbnail_url' => null,
            'wpnn_embed_color' => '#c65b3f',
            'wpnn_footer_text' => 'WPNN desk',
            'wpnn_show_timestamp' => true,
            'wpnn_enabled' => false,
        ]);
    }
}
