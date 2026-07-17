<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscordRole extends Model
{
    protected $fillable = [
        'discord_id',
        'name',
        'color',
        'position',
        'is_managed',
        'member_count',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_managed' => 'boolean',
            'synced_at' => 'datetime',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(DiscordRoleMember::class);
    }
}
