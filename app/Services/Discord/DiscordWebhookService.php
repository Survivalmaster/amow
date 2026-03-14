<?php

namespace App\Services\Discord;

use App\Models\DiscordSetting;
use Illuminate\Http\Client\Factory as HttpFactory;
use RuntimeException;

class DiscordWebhookService
{
    public function __construct(
        private readonly HttpFactory $http,
    ) {
    }

    public function postWpnnAnnouncement(
        DiscordSetting $settings,
        string $headline,
        string $announcement,
        string $authorName,
        ?string $imageUrl = null,
    ): void {
        if (! $settings->wpnn_enabled) {
            throw new RuntimeException('WPNN announcements are disabled.');
        }

        if (! $settings->wpnn_webhook_url) {
            throw new RuntimeException('WPNN webhook URL is not configured.');
        }

        $embed = [
            'title' => $headline,
            'description' => $announcement,
            'color' => hexdec(ltrim($settings->wpnn_embed_color, '#')),
            'footer' => [
                'text' => trim($settings->wpnn_footer_text.' | Posted by '.$authorName),
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        if ($imageUrl) {
            $embed['image'] = ['url' => $imageUrl];
        }

        $this->http
            ->acceptJson()
            ->post($settings->wpnn_webhook_url, [
                'embeds' => [$embed],
            ])
            ->throw();
    }
}
