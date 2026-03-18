<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon_type',
        'icon_value',
        'icon_color',
        'icon_tooltip',
        'grants_admin_access',
        'admin_sections',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'grants_admin_access' => 'boolean',
            'admin_sections' => 'array',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function grantsAdminSection(string $section): bool
    {
        return in_array($section, $this->admin_sections ?? [], true);
    }
}
