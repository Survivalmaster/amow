<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'firepower',
        'cost',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'firepower' => 'integer',
            'cost' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
