<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscordCommand;
use App\Models\DiscordWebhook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiscordWebhookAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.discord', [
            'webhooks' => DiscordWebhook::query()->orderBy('name')->get(),
            'commands' => DiscordCommand::query()->with('webhook')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateWebhook($request);

        DiscordWebhook::query()->create([
            'name' => $validated['name'],
            'channel_id' => $validated['channel_id'],
            'webhook_url' => $validated['webhook_url'],
            'embed_color' => strtoupper($validated['embed_color']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Discord webhook created.');
    }

    public function update(Request $request, DiscordWebhook $discordWebhook): RedirectResponse
    {
        $validated = $this->validateWebhook($request, $discordWebhook->id);

        $discordWebhook->update([
            'name' => $validated['name'],
            'channel_id' => $validated['channel_id'],
            'webhook_url' => $validated['webhook_url'],
            'embed_color' => strtoupper($validated['embed_color']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Discord webhook updated.');
    }

    public function destroy(DiscordWebhook $discordWebhook): RedirectResponse
    {
        $discordWebhook->delete();

        return back()->with('status', 'Discord webhook deleted.');
    }

    public function storeCommand(Request $request): RedirectResponse
    {
        $validated = $this->validateCommand($request);

        DiscordCommand::query()->create([
            'discord_webhook_id' => $validated['discord_webhook_id'],
            'name' => $validated['name'],
            'command_name' => $validated['command_name'],
            'command_description' => $validated['command_description'] ?: null,
            'access_mode' => $validated['access_mode'],
            'role_id' => $validated['access_mode'] === 'role' ? $validated['role_id'] : null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Discord command created.');
    }

    public function updateCommand(Request $request, DiscordCommand $discordCommand): RedirectResponse
    {
        $validated = $this->validateCommand($request, $discordCommand->id);

        $discordCommand->update([
            'discord_webhook_id' => $validated['discord_webhook_id'],
            'name' => $validated['name'],
            'command_name' => $validated['command_name'],
            'command_description' => $validated['command_description'] ?: null,
            'access_mode' => $validated['access_mode'],
            'role_id' => $validated['access_mode'] === 'role' ? $validated['role_id'] : null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Discord command updated.');
    }

    public function destroyCommand(DiscordCommand $discordCommand): RedirectResponse
    {
        $discordCommand->delete();

        return back()->with('status', 'Discord command deleted.');
    }

    private function validateWebhook(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'channel_id' => ['required', 'string', 'max:255', Rule::unique('discord_webhooks', 'channel_id')->ignore($ignoreId)],
            'webhook_url' => ['required', 'url', 'max:2048'],
            'embed_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function validateCommand(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'discord_webhook_id' => ['required', 'exists:discord_webhooks,id'],
            'name' => ['required', 'string', 'max:255'],
            'command_name' => [
                'required',
                'string',
                'min:1',
                'max:32',
                'regex:/^[a-z0-9_-]+$/',
                Rule::unique('discord_commands', 'command_name')->ignore($ignoreId),
            ],
            'command_description' => ['nullable', 'string', 'max:100'],
            'access_mode' => ['required', Rule::in(['anyone', 'role'])],
            'role_id' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
