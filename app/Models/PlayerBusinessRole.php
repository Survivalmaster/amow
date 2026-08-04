<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayerBusinessRole extends Model
{
    protected $fillable = [
        'player_business_id',
        'name',
        'hourly_wage',
    ];

    protected function casts(): array
    {
        return [
            'hourly_wage' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(PlayerBusiness::class, 'player_business_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(PlayerBusinessMember::class);
    }
}
