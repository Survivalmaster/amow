<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $character = $request->user()->character()->with([
            'faction',
            'rank',
            'currentJob',
            'inventory',
            'licences',
        ])->firstOrFail();

        abort_unless($character->hasHomeItem(), 404);

        return view('home.index', [
            'character' => $character,
            'homeItems' => $character->homeItems(),
        ]);
    }
}
