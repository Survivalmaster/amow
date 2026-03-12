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
}
