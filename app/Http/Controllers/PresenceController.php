<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_path' => ['nullable', 'string', 'max:255'],
            'current_page_name' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->touchPresence(
            $validated['current_path'] ?? $request->path(),
            $validated['current_page_name'] ?? 'Unknown Page'
        );

        return response()->json(['ok' => true]);
    }
}
