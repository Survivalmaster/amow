<?php

namespace App\Services\Discord;

use App\Models\User;
use Illuminate\Support\Str;

class DiscordLinkService
{
    public function issueToken(User $user): User
    {
        $user->forceFill([
            'discord_link_token' => Str::upper(Str::random(12)),
            'discord_link_token_expires_at' => now()->addMinutes(15),
        ])->save();

        return $user->refresh();
    }

    public function clearToken(User $user): User
    {
        $user->forceFill([
            'discord_link_token' => null,
            'discord_link_token_expires_at' => null,
        ])->save();

        return $user->refresh();
    }

    public function unlink(User $user): User
    {
        $user->forceFill([
            'discord_user_id' => null,
            'discord_username' => null,
            'discord_avatar' => null,
            'discord_linked_at' => null,
            'discord_link_token' => null,
            'discord_link_token_expires_at' => null,
        ])->save();

        return $user->refresh();
    }

    public function completeLink(
        User $user,
        string $discordUserId,
        string $discordUsername,
        ?string $discordAvatar = null,
    ): User {
        $user->forceFill([
            'discord_user_id' => $discordUserId,
            'discord_username' => $discordUsername,
            'discord_avatar' => $discordAvatar,
            'discord_linked_at' => now(),
            'discord_link_token' => null,
            'discord_link_token_expires_at' => null,
        ])->save();

        return $user->refresh();
    }
}
