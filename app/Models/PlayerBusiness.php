<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayerBusiness extends Model
{
    public const TYPES = [
        'sells_items' => 'Sells Items',
        'buys_items' => 'Buys Items',
        'creates_items_on_order' => 'Creates Items On Order',
        'services' => 'Services',
    ];

    public const ICONS = [
        'fa-solid fa-store' => 'Storefront',
        'fa-solid fa-hammer' => 'Workshop',
        'fa-solid fa-utensils' => 'Food Stall',
        'fa-solid fa-truck-ramp-box' => 'Trading Post',
        'fa-solid fa-scroll' => 'Contract Office',
        'fa-solid fa-flask' => 'Laboratory',
        'fa-solid fa-shirt' => 'Tailor',
        'fa-solid fa-shield-halved' => 'Armoury',
    ];

    protected $fillable = [
        'owner_character_id',
        'faction_id',
        'licence_id',
        'name',
        'slug',
        'icon_class',
        'business_type',
        'description',
        'bank_credits',
    ];

    protected function casts(): array
    {
        return [
            'bank_credits' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'owner_character_id');
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function licence(): BelongsTo
    {
        return $this->belongsTo(Licence::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(PlayerBusinessMenuItem::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(PlayerBusinessRole::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(PlayerBusinessMember::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PlayerBusinessLog::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->business_type] ?? str($this->business_type)->replace('_', ' ')->title()->toString();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
