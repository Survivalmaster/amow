<?php

namespace App\Policies;

use App\Models\MapHex;
use App\Models\User;

class MapHexPolicy
{
    public function view(?User $user, MapHex $mapHex): bool
    {
        return true;
    }

    public function update(User $user, MapHex $mapHex): bool
    {
        return $this->manageTerritory($user);
    }

    public function claim(User $user, MapHex $mapHex): bool
    {
        return $this->manageTerritory($user);
    }

    public function removeClaim(User $user, MapHex $mapHex): bool
    {
        return $this->manageTerritory($user);
    }

    private function manageTerritory(User $user): bool
    {
        $user->loadMissing('permissions');

        return $user->canAccessAdmin() || $user->hasPermission('territory-management');
    }
}
