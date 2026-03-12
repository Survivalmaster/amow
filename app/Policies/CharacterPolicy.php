<?php

namespace App\Policies;

use App\Models\Character;
use App\Models\User;

class CharacterPolicy
{
    public function view(User $user, Character $character): bool
    {
        return $user->id === $character->user_id || $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->character === null;
    }

    public function update(User $user, Character $character): bool
    {
        return $user->id === $character->user_id || $user->is_admin;
    }
}
