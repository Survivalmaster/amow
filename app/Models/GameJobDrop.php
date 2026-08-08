<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameJobDrop extends Model
{
    protected $fillable = [
        'game_job_id',
        'item_id',
        'min_tier',
        'max_tier',
        'min_quantity',
        'max_quantity',
        'drop_chance_percent',
    ];

    protected function casts(): array
    {
        return [
            'min_tier' => 'integer',
            'max_tier' => 'integer',
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'drop_chance_percent' => 'decimal:2',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(GameJob::class, 'game_job_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
