<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class Character extends Model
{
    protected $fillable = [
        'user_id',
        'faction_id',
        'name',
        'age',
        'biography',
        'starting_occupation',
        'current_job_id',
        'plastic_credits',
        'rank_id',
        'influence_score',
        'military_score',
        'economic_score',
        'level',
        'experience_points',
        'health_points',
        'stamina_points',
        'armor_points',
        'role_type',
        'is_business_owner',
        'is_nation_leader',
        'last_worked_at',
        'job_changed_at',
        'last_business_payout_at',
    ];

    protected function casts(): array
    {
        return [
            'is_business_owner' => 'boolean',
            'is_nation_leader' => 'boolean',
            'last_worked_at' => 'datetime',
            'job_changed_at' => 'datetime',
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

    public function currentJob(): BelongsTo
    {
        return $this->belongsTo(GameJob::class, 'current_job_id');
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

    public function nationRequisitions(): HasMany
    {
        return $this->hasMany(NationRequisition::class, 'submitted_by_character_id');
    }

    public function getDisplayedJobNameAttribute(): string
    {
        return $this->currentJob?->name ?? $this->starting_occupation;
    }

    public function experienceRequiredForNextLevel(): int
    {
        return 100 + ($this->level * 50);
    }

    public function gainExperience(int $amount): int
    {
        if ($amount <= 0) {
            return 0;
        }

        $leveledUp = 0;
        $experiencePoints = $this->experience_points + $amount;
        $level = $this->level;

        while ($experiencePoints >= (100 + ($level * 50))) {
            $experiencePoints -= 100 + ($level * 50);
            $level++;
            $leveledUp++;
        }

        $this->forceFill([
            'level' => $level,
            'experience_points' => $experiencePoints,
        ])->save();

        return $leveledUp;
    }

    public function workCooldownEndsAt(): ?Carbon
    {
        if (! $this->last_worked_at) {
            return null;
        }

        return $this->last_worked_at->copy()->addMinutes($this->currentJob?->work_cooldown_minutes ?? 5);
    }

    public function canChangeJob(): bool
    {
        if (! $this->job_changed_at) {
            return true;
        }

        return $this->job_changed_at->lte(now()->subDay());
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

    public function homeItems(): Collection
    {
        return $this->inventory->filter(fn (Item $item) => $item->is_home)->values();
    }

    public function hasHomeItem(): bool
    {
        return $this->homeItems()->isNotEmpty();
    }

    public function inventorySlotCapacity(): int
    {
        return 12 + $this->inventory->sum(function (Item $item) {
            return max(0, (int) $item->inventory_slot_bonus) * max(1, (int) $item->pivot->quantity);
        });
    }

    public function inventorySlotsUsed(): int
    {
        return $this->inventory->count();
    }

    public function inventorySlotsRemaining(): int
    {
        return max(0, $this->inventorySlotCapacity() - $this->inventorySlotsUsed());
    }

    public function canStoreAdditionalItem(Item $item): bool
    {
        if ($this->inventory->contains('id', $item->id)) {
            return true;
        }

        return ($this->inventorySlotsUsed() + 1) <= ($this->inventorySlotCapacity() + max(0, (int) $item->inventory_slot_bonus));
    }

    public function canLeadNation(): bool
    {
        return (bool) $this->is_nation_leader || (bool) $this->user?->hasPermission('nation-leader');
    }
}
