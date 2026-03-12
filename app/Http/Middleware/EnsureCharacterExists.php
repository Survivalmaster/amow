<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCharacterExists
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->character) {
            return redirect()->route('factions.index')
                ->with('status', 'Choose a faction and create your character before entering Plastica.');
        }

        return $next($request);
    }
}
