<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PresenceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_path' => ['nullable', 'string', 'max:255'],
            'current_page_name' => ['nullable', 'string', 'max:255'],
            'current_activity_text' => ['nullable', 'string', 'max:255'],
        ]);

        $currentPath = $validated['current_path'] ?? $request->path();
        $currentPageName = $this->resolvePageName(
            $validated['current_page_name'] ?? null,
            $currentPath
        );
        $currentActivityText = filled($validated['current_activity_text'] ?? null)
            ? trim((string) $validated['current_activity_text'])
            : null;

        $request->user()->touchPresence(
            $currentPath,
            $currentPageName,
            $currentActivityText,
            true
        );

        return response()->json(['ok' => true]);
    }

    private function resolvePageName(?string $pageName, string $path): string
    {
        if (filled($pageName) && $pageName !== 'Unknown Page') {
            return $pageName;
        }

        $normalizedPath = trim($path, '/');

        if ($normalizedPath === '') {
            return 'Home';
        }

        return (string) Str::of($normalizedPath)
            ->replace(['/', '-', '_', '.'], ' ')
            ->title();
    }
}
