<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscordRoleMember extends Model
{
    protected $fillable = [
        'discord_role_id',
        'discord_user_id',
        'username',
        'display_name',
        'avatar_url',
        'joined_at',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(DiscordRole::class, 'discord_role_id');
    }
}
