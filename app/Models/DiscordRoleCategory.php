<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordRoleCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
    ];
}
