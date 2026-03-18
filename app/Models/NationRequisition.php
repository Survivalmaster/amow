<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NationRequisition extends Model
{
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_BEING_REVIEWED = 'being_reviewed';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DENIED = 'denied';

    protected $fillable = [
        'faction_id',
        'submitted_by_character_id',
        'title',
        'details',
        'status',
        'admin_reason',
        'reviewed_by_user_id',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'submitted_by_character_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public static function openStatuses(): array
    {
        return [
            self::STATUS_SUBMITTED,
            self::STATUS_BEING_REVIEWED,
        ];
    }
}
