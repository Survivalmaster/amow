<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapHex extends Model
{
    use HasFactory;

    public const TYPE_CLAIMABLE = 'claimable';
    public const TYPE_WATER = 'water';
    public const TYPE_BLOCKED = 'blocked';
    public const TYPE_DECORATIVE = 'decorative';
    public const TYPE_INACTIVE = 'inactive';

    public const TILE_TYPES = [
        self::TYPE_CLAIMABLE,
        self::TYPE_WATER,
        self::TYPE_BLOCKED,
        self::TYPE_DECORATIVE,
        self::TYPE_INACTIVE,
    ];

    protected $fillable = [
        'grid_column',
        'grid_row',
        'centre_x',
        'centre_y',
        'polygon_coordinates',
        'tile_type',
        'faction_id',
        'terrain_type',
        'claim_strength',
        'is_visible',
        'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'centre_x' => 'decimal:3',
            'centre_y' => 'decimal:3',
            'polygon_coordinates' => 'array',
            'claim_strength' => 'integer',
            'is_visible' => 'boolean',
            'claimed_at' => 'datetime',
        ];
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function isClaimable(): bool
    {
        return $this->tile_type === self::TYPE_CLAIMABLE && $this->is_visible;
    }
}
