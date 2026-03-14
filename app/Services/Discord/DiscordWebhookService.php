<?php

namespace App\Services\Discord;

use App\Models\DiscordWebhook;
use Illuminate\Http\Client\Factory as HttpFactory;
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
}
