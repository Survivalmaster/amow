<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AccountIcon extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon_type',
        'icon_value',
        'color',
        'tooltip',
        'sort_order',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
