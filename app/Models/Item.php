<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    public const TYPES = [
        'consumable' => 'Consumable',
        'weapon' => 'Weapon',
        'armor' => 'Armor',
        'utility' => 'Utility',
        'tool' => 'Tool',
        'material' => 'Material',
        'trade' => 'Trade Good',
        'backpack' => 'Backpack',
        'building' => 'Building',
        'business' => 'Business',
        'military' => 'Military',
        'misc' => 'Miscellaneous',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'icon_class',
        'is_home',
        'is_building',
        'is_buyable',
        'footprint_width',
        'footprint_height',
        'build_time_minutes',
        'produced_by_building_item_id',
        'inventory_slot_bonus',
        'price',
        'required_rank_id',
        'required_level',
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

    public function landBuildings(): HasMany
    {
        return $this->hasMany(CharacterLandBuilding::class);
    }

    public function producingBuilding(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'produced_by_building_item_id');
    }

    public function producedItems(): HasMany
    {
        return $this->hasMany(Item::class, 'produced_by_building_item_id');
    }

    protected function casts(): array
    {
        return [
            'is_home' => 'boolean',
            'is_building' => 'boolean',
            'is_buyable' => 'boolean',
            'footprint_width' => 'integer',
            'footprint_height' => 'integer',
            'build_time_minutes' => 'integer',
            'produced_by_building_item_id' => 'integer',
            'inventory_slot_bonus' => 'integer',
            'required_level' => 'integer',
        ];
    }

    public function getDisplayIconClassAttribute(): string
    {
        if (filled($this->icon_class)) {
            return $this->icon_class;
        }

        return match ($this->type) {
            'consumable' => 'fa-solid fa-flask',
            'weapon' => 'fa-solid fa-gun',
            'armor' => 'fa-solid fa-shield-halved',
            'home' => 'fa-solid fa-house',
            'building' => 'fa-solid fa-tents',
            'backpack' => 'fa-solid fa-backpack',
            'military' => 'fa-solid fa-shield-halved',
            'business' => 'fa-solid fa-briefcase',
            'trade' => 'fa-solid fa-scroll',
            'tool' => 'fa-solid fa-screwdriver-wrench',
            'material' => 'fa-solid fa-boxes-stacked',
            'utility' => 'fa-solid fa-screwdriver-wrench',
            default => 'fa-solid fa-cube',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? str($this->type)->replace('_', ' ')->title()->toString();
    }
}
