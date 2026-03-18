<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSectionAccess
{
    public function handle(Request $request, Closure $next, string $section): Response
    {
        $user = $request->user()?->loadMissing('permissions');

        abort_unless($user && $user->canAccessAdminSection($section), 403);

        return $next($request);
    }
}
