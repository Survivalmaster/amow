<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class BannedAccountController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()?->isBanned(), 404);

        return view('auth.banned', [
            'user' => $request->user(),
        ]);
    }
}
