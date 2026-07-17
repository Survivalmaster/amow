<?php

namespace App\Http\Controllers;

use App\Support\DiscordRosterBuilder;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicDiscordRosterController extends Controller
{
    public function show(string $nation, DiscordRosterBuilder $rosterBuilder): View
    {
        $roster = $rosterBuilder->build();
        $selectedNation = $roster['nations']->firstWhere('key', Str::slug($nation));

        if (! $selectedNation) {
            throw new NotFoundHttpException;
        }

        return view('discord.public-roster', [
            'nation' => $selectedNation,
            'nations' => $roster['nations'],
            'lastSyncedAt' => $roster['last_synced_at'],
        ]);
    }
}
