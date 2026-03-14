<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscordSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscordAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.discord', [
            'settings' => DiscordSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wpnn_webhook_url' => ['nullable', 'url', 'max:2048'],
            'wpnn_embed_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'wpnn_footer_text' => ['required', 'string', 'max:255'],
            'wpnn_enabled' => ['nullable', 'boolean'],
        ]);

        DiscordSetting::current()->update([
            'wpnn_webhook_url' => $validated['wpnn_webhook_url'] ?: null,
            'wpnn_embed_color' => strtoupper($validated['wpnn_embed_color']),
            'wpnn_footer_text' => $validated['wpnn_footer_text'],
            'wpnn_enabled' => $request->boolean('wpnn_enabled'),
        ]);

        return back()->with('status', 'Discord settings updated.');
    }
}
