<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'city_id',
        'name',
        'slug',
        'description',
        'required_rank_id',
        'required_licence_id',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function requiredRank(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'required_rank_id');
    }

    public function requiredLicence(): BelongsTo
    {
        return $this->belongsTo(Licence::class, 'required_licence_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
