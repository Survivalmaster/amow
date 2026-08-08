<?php

namespace App\Services\Discord;

use App\Models\Changelog;
use App\Models\DiscordWebhook;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Str;
use RuntimeException;

class DiscordWebhookService
{
    public function __construct(
        private readonly HttpFactory $http,
    ) {
    }

    public function postWpnnAnnouncement(
        DiscordWebhook $webhook,
        string $headline,
        string $announcement,
        string $authorName,
        ?string $imageUrl = null,
    ): void {
        if (! $webhook->is_active) {
            throw new RuntimeException('This Discord webhook is disabled.');
        }

        if (! $webhook->webhook_url) {
            throw new RuntimeException('This Discord webhook URL is not configured.');
        }

        $embed = [
            'title' => $headline,
            'description' => $announcement,
            'color' => hexdec(ltrim($webhook->embed_color, '#')),
            'footer' => [
                'text' => trim($webhook->name.' | Posted by '.$authorName),
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        if ($imageUrl) {
            $embed['image'] = ['url' => $imageUrl];
        }

        $this->http
            ->acceptJson()
            ->post($webhook->webhook_url, [
                'embeds' => [$embed],
            ])
            ->throw();
    }

    public function postChangelog(DiscordWebhook $webhook, Changelog $changelog): void
    {
        if (! $webhook->is_active) {
            throw new RuntimeException('This Discord webhook is disabled.');
        }

        if (! $webhook->webhook_url) {
            throw new RuntimeException('This Discord webhook URL is not configured.');
        }

        $this->http
            ->acceptJson()
            ->post($webhook->webhook_url, [
                'embeds' => [$this->changelogEmbed($changelog, $webhook->embed_color, $webhook->name.' | Changelog')],
            ])
            ->throw();
    }

    public function changelogEmbed(Changelog $changelog, string $color = '#7EAD59', string $footer = 'AMOW Changelog'): array
    {
        $groups = $changelog->groupedFeatures();
        $releasedAt = $changelog->released_at ?? now();
        $changelogUrl = route('changelogs.index').'#changelog-'.$changelog->id;
        $summaryLines = [
            '✨ Added: '.count($groups['Added']),
            '🛠️ Edited: '.count($groups['Edited']),
            '🗑️ Removed: '.count($groups['Removed']),
            '',
            'Date: '.$releasedAt->format('d/m/Y'),
            'Version: '.$changelog->version,
            'Full changelog: '.$changelogUrl,
        ];
        $fields = [];

        foreach ($groups as $group => $features) {
            if ($features === []) {
                continue;
            }

            $emoji = match ($group) {
                'Added' => '✨',
                'Edited' => '🛠️',
                'Removed' => '🗑️',
                default => '•',
            };

            $fields[] = [
                'name' => "{$emoji} {$group}",
                'value' => Str::limit(collect($features)
                    ->map(fn (string $feature): string => '- '.Str::limit($feature, 180))
                    ->implode("\n"), 1000),
                'inline' => false,
            ];
        }

        if (filled($changelog->body)) {
            $fields[] = [
                'name' => 'Summary',
                'value' => Str::limit($changelog->body, 1000),
                'inline' => false,
            ];
        }

        return [
            'title' => $changelog->title.' - '.$releasedAt->format('d/m/Y').' - Version '.$changelog->version,
            'description' => trim(collect([
                filled($changelog->summary) ? $changelog->summary : 'A new AMOW update has been released.',
                '',
                implode("\n", $summaryLines),
            ])->implode("\n")),
            'color' => hexdec(ltrim($color, '#')),
            'fields' => $fields,
            'footer' => [
                'text' => $footer.' | '.now()->format('d/m/Y H:i'),
            ],
            'timestamp' => ($changelog->released_at ?? now())->toIso8601String(),
        ];
    }
}
