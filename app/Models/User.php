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
        'last_seen_at',
        'current_path',
        'current_page_name',
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
            'last_seen_at' => 'datetime',
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

    public function getDiscordAvatarUrlAttribute(): ?string
    {
        if (! $this->discord_user_id || ! $this->discord_avatar) {
            return null;
        }

        if (str_starts_with($this->discord_avatar, 'http://') || str_starts_with($this->discord_avatar, 'https://')) {
            return $this->discord_avatar;
        }

        $extension = str_starts_with($this->discord_avatar, 'a_') ? 'gif' : 'png';

        return "https://cdn.discordapp.com/avatars/{$this->discord_user_id}/{$this->discord_avatar}.{$extension}?size=256";
    }

    public function touchPresence(?string $currentPath = null, ?string $currentPageName = null): void
    {
        $attributes = [];

        if (! $this->last_seen_at || $this->last_seen_at->lt(now()->subSeconds(5))) {
            $attributes['last_seen_at'] = now();
        }

        if ($currentPath !== null && $this->current_path !== $currentPath) {
            $attributes['current_path'] = $currentPath;
        }

        if ($currentPageName !== null && $this->current_page_name !== $currentPageName) {
            $attributes['current_page_name'] = $currentPageName;
        }

        if ($attributes !== []) {
            $this->forceFill($attributes)->save();
        }
    }

    public function isOnline(): bool
    {
        return (bool) $this->last_seen_at?->gt(now()->subMinutes(5));
    }
}
