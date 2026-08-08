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
        $fields = [];

        foreach ($changelog->groupedFeatures() as $group => $features) {
            if ($features === []) {
                continue;
            }

            $fields[] = [
                'name' => $group,
                'value' => Str::limit(collect($features)
                    ->map(fn (string $feature): string => '- '.Str::limit($feature, 180))
                    ->implode("\n"), 1000),
                'inline' => false,
            ];
        }

        if (filled($changelog->body)) {
            $fields[] = [
                'name' => 'Notes',
                'value' => Str::limit($changelog->body, 1000),
                'inline' => false,
            ];
        }

        return [
            'title' => "AMOW Update {$changelog->version}",
            'description' => trim("**{$changelog->title}**\n\n".($changelog->summary ?? '')),
            'color' => hexdec(ltrim($color, '#')),
            'fields' => $fields,
            'footer' => [
                'text' => $footer,
            ],
            'timestamp' => ($changelog->released_at ?? now())->toIso8601String(),
        ];
    }
}
