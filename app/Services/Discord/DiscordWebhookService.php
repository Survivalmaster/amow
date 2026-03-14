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
        ];

        if ($settings->wpnn_author_name) {
            $embed['author'] = [
                'name' => $settings->wpnn_author_name,
            ];

            if ($settings->wpnn_author_icon_url) {
                $embed['author']['icon_url'] = $settings->wpnn_author_icon_url;
            }
        }

        if ($settings->wpnn_thumbnail_url) {
            $embed['thumbnail'] = ['url' => $settings->wpnn_thumbnail_url];
        }

        if ($settings->wpnn_footer_text) {
            $embed['footer'] = [
                'text' => trim($settings->wpnn_footer_text.' | Posted by '.$authorName),
            ];
        }

        if ($settings->wpnn_show_timestamp) {
            $embed['timestamp'] = now()->toIso8601String();
        }

        if ($imageUrl) {
            $embed['image'] = ['url' => $imageUrl];
        }

        $payload = [
            'embeds' => [$embed],
        ];

        if ($settings->wpnn_message_prefix) {
            $payload['content'] = $settings->wpnn_message_prefix;
        }

        $this->http
            ->acceptJson()
            ->post($settings->wpnn_webhook_url, $payload)
            ->throw();
    }
}
