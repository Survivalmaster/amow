<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Character extends Model
{
    protected $fillable = [
        'user_id',
        'faction_id',
        'name',
        'age',
        'biography',
        'starting_occupation',
        'plastic_credits',
        'rank_id',
        'influence_score',
        'military_score',
        'economic_score',
        'health_points',
        'role_type',
        'is_business_owner',
        'last_worked_at',
        'last_business_payout_at',
    ];

    protected function casts(): array
    {
        return [
            'is_business_owner' => 'boolean',
            'last_worked_at' => 'datetime',
            'last_business_payout_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    public function licences(): BelongsToMany
    {
        return $this->belongsToMany(Licence::class, 'character_licences')->withTimestamps();
    }

    public function inventory(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'character_items')->withPivot('quantity')->withTimestamps();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function holdings(): HasMany
    {
        return $this->hasMany(StockHolding::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function hasLicence(string $slug): bool
    {
        return $this->licences->contains('slug', $slug);
    }

    public function canAccessLocation(Location $location): bool
    {
        if ($location->city->faction_id !== $this->faction_id) {
            return false;
        }

        if ($location->required_rank_id && $this->rank->order_index < $location->requiredRank->order_index) {
            return false;
        }

        if ($location->required_licence_id && ! $this->licences->contains('id', $location->required_licence_id)) {
            return false;
        }

        return true;
    }

    public function canPurchaseItem(Item $item): bool
    {
        if ($item->required_role_type && $item->required_role_type !== $this->role_type) {
            return false;
        }

        if ($item->required_rank_id && $this->rank->order_index < $item->requiredRank->order_index) {
            return false;
        }

        if ($item->required_licence_id && ! $this->licences->contains('id', $item->required_licence_id)) {
            return false;
        }

        return $item->stock === null || $item->stock > 0;
    }
}
