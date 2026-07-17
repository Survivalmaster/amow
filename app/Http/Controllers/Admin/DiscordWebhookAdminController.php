<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscordCommand;
use App\Models\DiscordWebhook;
use App\Services\Discord\AdminActionLogger;
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

    public function store(Request $request, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $validated = $this->validateWebhook($request);

        $discordWebhook = DiscordWebhook::query()->create([
            'name' => $validated['name'],
            'channel_id' => $validated['channel_id'],
            'webhook_url' => $validated['webhook_url'],
            'embed_color' => strtoupper($validated['embed_color']),
            'is_active' => $request->boolean('is_active'),
        ]);
        $adminActionLogger->created($request->user(), 'Discord Webhook', $discordWebhook);

        return back()->with('status', 'Discord webhook created.');
    }

    public function update(Request $request, DiscordWebhook $discordWebhook, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($discordWebhook);
        $validated = $this->validateWebhook($request, $discordWebhook->id);

        $discordWebhook->update([
            'name' => $validated['name'],
            'channel_id' => $validated['channel_id'],
            'webhook_url' => $validated['webhook_url'],
            'embed_color' => strtoupper($validated['embed_color']),
            'is_active' => $request->boolean('is_active'),
        ]);
        $adminActionLogger->updated($request->user(), 'Discord Webhook', $before, $discordWebhook);

        return back()->with('status', 'Discord webhook updated.');
    }

    public function destroy(Request $request, DiscordWebhook $discordWebhook, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $adminActionLogger->snapshot($discordWebhook);
        $discordWebhook->delete();
        $adminActionLogger->deleted($request->user(), 'Discord Webhook', $snapshot);

        return back()->with('status', 'Discord webhook deleted.');
    }

    public function storeCommand(Request $request, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $validated = $this->validateCommand($request);

        $payload = [
            'discord_webhook_id' => $validated['handler_key'] === 'webhook_post' ? $validated['discord_webhook_id'] : null,
            'name' => $validated['name'],
            'command_name' => $validated['command_name'],
            'command_description' => $validated['command_description'] ?: null,
            'handler_key' => $validated['handler_key'],
            'access_mode' => $validated['access_mode'],
            'role_id' => $validated['access_mode'] === 'role' ? $validated['role_id'] : null,
            'allow_any_channel' => $request->boolean('allow_any_channel'),
            'command_options' => $this->commandOptionsForHandler($validated['handler_key']),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($validated['handler_key'] === 'pray_to_deity') {
            $discordCommand = DiscordCommand::query()->where('command_name', $validated['command_name'])->first();

            if ($discordCommand) {
                $before = $adminActionLogger->snapshot($discordCommand);
                $discordCommand->fill($payload);
                $discordCommand->save();
                $adminActionLogger->updated($request->user(), 'Discord Command', $before, $discordCommand);
            } else {
                $discordCommand = DiscordCommand::query()->create($payload);
                $adminActionLogger->created($request->user(), 'Discord Command', $discordCommand);
            }
        } else {
            $discordCommand = DiscordCommand::query()->create($payload);
            $adminActionLogger->created($request->user(), 'Discord Command', $discordCommand);
        }

        return back()->with('status', 'Discord command created.');
    }

    public function updateCommand(Request $request, DiscordCommand $discordCommand, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($discordCommand);
        $validated = $this->validateCommand($request, $discordCommand->id);

        $discordCommand->update([
            'discord_webhook_id' => $validated['handler_key'] === 'webhook_post' ? $validated['discord_webhook_id'] : null,
            'name' => $validated['name'],
            'command_name' => $validated['command_name'],
            'command_description' => $validated['command_description'] ?: null,
            'handler_key' => $validated['handler_key'],
            'access_mode' => $validated['access_mode'],
            'role_id' => $validated['access_mode'] === 'role' ? $validated['role_id'] : null,
            'allow_any_channel' => $request->boolean('allow_any_channel'),
            'command_options' => $this->commandOptionsForHandler($validated['handler_key']),
            'is_active' => $request->boolean('is_active'),
        ]);
        $adminActionLogger->updated($request->user(), 'Discord Command', $before, $discordCommand);

        return back()->with('status', 'Discord command updated.');
    }

    public function destroyCommand(Request $request, DiscordCommand $discordCommand, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $adminActionLogger->snapshot($discordCommand);
        $discordCommand->delete();
        $adminActionLogger->deleted($request->user(), 'Discord Command', $snapshot);

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
        $validated = $request->validate([
            'discord_webhook_id' => ['nullable', 'exists:discord_webhooks,id'],
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
            'handler_key' => ['required', Rule::in(['webhook_post', 'pray_to_deity'])],
            'access_mode' => ['required', Rule::in(['anyone', 'role'])],
            'role_id' => ['nullable', 'string', 'max:255'],
            'allow_any_channel' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (($validated['handler_key'] ?? 'webhook_post') === 'webhook_post' && empty($validated['discord_webhook_id'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'discord_webhook_id' => 'A linked webhook is required for webhook post commands.',
            ]);
        }

        if (($validated['handler_key'] ?? null) === 'pray_to_deity') {
            $validated['name'] = 'Pray to Marble or Obsidian';
            $validated['command_name'] = 'amowpray';
            $validated['command_description'] = 'Ask Marble or Obsidian to bless or smite you.';
            $validated['access_mode'] = 'anyone';
            $validated['role_id'] = null;
            $validated['allow_any_channel'] = true;
        }

        return $validated;
    }

    private function commandOptionsForHandler(string $handlerKey): ?array
    {
        return match ($handlerKey) {
            'pray_to_deity' => [[
                'name' => 'deity',
                'description' => 'Choose the god you want to pray to.',
                'type' => 'string',
                'required' => true,
                'choices' => [
                    ['name' => 'Marble', 'value' => 'Marble'],
                    ['name' => 'Obsidian', 'value' => 'Obsidian'],
                ],
            ]],
            default => null,
        };
    }
}
