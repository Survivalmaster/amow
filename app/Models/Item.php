<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Item extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'icon_class',
        'is_home',
        'inventory_slot_bonus',
        'price',
        'required_rank_id',
        'required_role_type',
        'required_licence_id',
        'stock',
    ];

    public function requiredRank(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'required_rank_id');
    }

    public function requiredLicence(): BelongsTo
    {
        return $this->belongsTo(Licence::class, 'required_licence_id');
    }

    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'character_items')->withPivot('quantity')->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'is_home' => 'boolean',
            'inventory_slot_bonus' => 'integer',
        ];
    }

    public function getDisplayIconClassAttribute(): string
    {
        if (filled($this->icon_class)) {
            return $this->icon_class;
        }

        return match ($this->type) {
            'home' => 'fa-solid fa-house',
            'backpack' => 'fa-solid fa-backpack',
            'military' => 'fa-solid fa-shield-halved',
            'business' => 'fa-solid fa-briefcase',
            'trade' => 'fa-solid fa-scroll',
            'utility' => 'fa-solid fa-screwdriver-wrench',
            default => 'fa-solid fa-cube',
        };
    }
}
