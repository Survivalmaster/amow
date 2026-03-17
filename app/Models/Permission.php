<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'account_icon_id',
        'grants_admin_access',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'grants_admin_access' => 'boolean',
        ];
    }

    public function accountIcon(): BelongsTo
    {
        return $this->belongsTo(AccountIcon::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
