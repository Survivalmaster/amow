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
            'wpnn_message_prefix' => ['nullable', 'string', 'max:500'],
            'wpnn_author_name' => ['nullable', 'string', 'max:255'],
            'wpnn_author_icon_url' => ['nullable', 'url', 'max:2048'],
            'wpnn_thumbnail_url' => ['nullable', 'url', 'max:2048'],
            'wpnn_embed_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'wpnn_footer_text' => ['required', 'string', 'max:255'],
            'wpnn_show_timestamp' => ['nullable', 'boolean'],
            'wpnn_enabled' => ['nullable', 'boolean'],
        ]);

        DiscordSetting::current()->update([
            'wpnn_webhook_url' => $validated['wpnn_webhook_url'] ?: null,
            'wpnn_message_prefix' => $validated['wpnn_message_prefix'] ?: null,
            'wpnn_author_name' => $validated['wpnn_author_name'] ?: null,
            'wpnn_author_icon_url' => $validated['wpnn_author_icon_url'] ?: null,
            'wpnn_thumbnail_url' => $validated['wpnn_thumbnail_url'] ?: null,
            'wpnn_embed_color' => strtoupper($validated['wpnn_embed_color']),
            'wpnn_footer_text' => $validated['wpnn_footer_text'],
            'wpnn_show_timestamp' => $request->boolean('wpnn_show_timestamp'),
            'wpnn_enabled' => $request->boolean('wpnn_enabled'),
        ]);

        return back()->with('status', 'Discord settings updated.');
    }
}
