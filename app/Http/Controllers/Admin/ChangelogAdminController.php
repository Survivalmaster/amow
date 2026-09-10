<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Changelog;
use App\Services\Discord\AdminActionLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ChangelogAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.changelogs', [
            'changelogs' => Changelog::query()->with('webhook')->latest('released_at')->latest()->get(),
            'nextVersion' => Changelog::nextVersion(),
            'defaultDiscordChannelId' => Changelog::latestDiscordChannelId(),
            'defaultReleasedAt' => now()->format('Y-m-d\TH:i'),
        ]);
    }

    public function store(
        Request $request,
        AdminActionLogger $adminActionLogger
    ): RedirectResponse {
        $changelog = Changelog::query()->create($this->validatedData($request));
        $adminActionLogger->created($request->user(), 'Changelog', $changelog);

        return back()->with('status', 'Changelog saved.');
    }

    public function update(
        Request $request,
        Changelog $changelog,
        AdminActionLogger $adminActionLogger
    ): RedirectResponse {
        $before = $adminActionLogger->snapshot($changelog);
        $changelog->update($this->validatedData($request, $changelog));
        $adminActionLogger->updated($request->user(), 'Changelog', $before, $changelog);

        return back()->with('status', 'Changelog updated.');
    }

    public function publish(
        Request $request,
        Changelog $changelog,
        AdminActionLogger $adminActionLogger
    ): RedirectResponse {
        if (blank($changelog->discord_channel_id)) {
            throw ValidationException::withMessages([
                'discord_channel_id' => 'Add a Discord channel ID before publishing this changelog.',
            ]);
        }

        if (blank(config('services.discord.bot_sync_secret')) && blank(config('services.discord.linking_secret'))) {
            throw ValidationException::withMessages([
                'discord_channel_id' => 'Set DISCORD_BOT_SYNC_SECRET or DISCORD_LINKING_SECRET on the website before publishing changelogs.',
            ]);
        }

        $before = $adminActionLogger->snapshot($changelog);
        $changelog->forceFill([
            'status' => 'released',
            'released_at' => $changelog->released_at ?? now(),
            'discord_message_sent_at' => null,
        ])->save();

        $adminActionLogger->updated($request->user(), 'Changelog', $before, $changelog);

        return back()->with('status', 'Changelog queued for Discord delivery.');
    }

    public function destroy(Request $request, Changelog $changelog, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $adminActionLogger->snapshot($changelog);
        $changelog->delete();
        $adminActionLogger->deleted($request->user(), 'Changelog', $snapshot);

        return back()->with('status', 'Changelog deleted.');
    }

    private function validatedData(Request $request, ?Changelog $changelog = null): array
    {
        $validated = $request->validate([
            'discord_webhook_id' => ['nullable', 'exists:discord_webhooks,id'],
            'discord_channel_id' => ['nullable', 'string', 'max:255', 'regex:/^[0-9]{15,25}$/'],
            'version' => ['required', 'string', 'max:40', Rule::unique('changelogs', 'version')->ignore($changelog?->id)],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'added_features_text' => ['nullable', 'string', 'max:6000'],
            'edited_features_text' => ['nullable', 'string', 'max:6000'],
            'removed_features_text' => ['nullable', 'string', 'max:6000'],
            'released_at' => ['nullable', 'date'],
        ]);

        $discordWebhookId = $validated['discord_webhook_id'] ?? null;
        $discordChannelId = $validated['discord_channel_id'] ?? null;

        return [
            'discord_webhook_id' => $discordWebhookId ?: null,
            'discord_channel_id' => $discordChannelId ?: null,
            'version' => $validated['version'],
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?: null,
            'features' => $this->linesFromText($validated['added_features_text'] ?? ''),
            'added_features' => $this->linesFromText($validated['added_features_text'] ?? ''),
            'edited_features' => $this->linesFromText($validated['edited_features_text'] ?? ''),
            'removed_features' => $this->linesFromText($validated['removed_features_text'] ?? ''),
            'status' => $changelog?->status ?? 'draft',
            'released_at' => $validated['released_at'] ?? $changelog?->released_at,
        ];
    }

    private function linesFromText(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
