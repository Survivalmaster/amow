<?php

namespace App\Http\Controllers;

use App\Services\Discord\DiscordLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class DiscordLinkController extends Controller
{
    public function store(Request $request, DiscordLinkService $linkService): RedirectResponse
    {
        $linkService->issueToken($request->user());

        return Redirect::route('profile.edit')->with('status', 'discord-link-issued');
    }

    public function destroy(Request $request, DiscordLinkService $linkService): RedirectResponse
    {
        $linkService->unlink($request->user());

        return Redirect::route('profile.edit')->with('status', 'discord-unlinked');
    }
}
