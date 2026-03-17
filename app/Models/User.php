<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'discord_user_id',
        'discord_username',
        'discord_avatar',
        'discord_link_token',
        'discord_link_token_expires_at',
        'discord_linked_at',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'discord_link_token_expires_at' => 'datetime',
            'discord_linked_at' => 'datetime',
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function character(): HasOne
    {
        return $this->hasOne(Character::class);
    }

    public function accountIcons(): BelongsToMany
    {
        return $this->belongsToMany(AccountIcon::class)->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions->contains(fn (Permission $permission) => $permission->slug === $slug);
    }

    public function canAccessAdmin(): bool
    {
        return $this->permissions->contains(fn (Permission $permission) => $permission->grants_admin_access);
    }

    public function permissionIcons(): Collection
    {
        return $this->permissions
            ->sortBy(fn (Permission $permission) => sprintf('%05d-%s', $permission->sort_order, $permission->name))
            ->map(fn (Permission $permission) => $permission->accountIcon)
            ->filter()
            ->values();
    }
}
