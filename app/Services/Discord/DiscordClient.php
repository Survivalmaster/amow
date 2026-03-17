<?php

namespace App\Services\Discord;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use RuntimeException;

class DiscordClient
{
    public function __construct(
        private readonly HttpFactory $http,
    ) {
    }

    public function sendMessage(string $channelId, string $content): Response
    {
        return $this->post($channelId, [
            'content' => $content,
        ]);
    }

    public function sendEmbedMessage(string $channelId, array $embed, ?string $content = null): Response
    {
        $payload = [
            'embeds' => [$embed],
        ];

        if ($content !== null && $content !== '') {
            $payload['content'] = $content;
        }

        return $this->post($channelId, $payload);
    }

    private function post(string $channelId, array $payload): Response
    {
        $token = (string) config('services.discord.bot_token');

        if ($token === '') {
            throw new RuntimeException('Discord bot token is not configured.');
        }

        return $this->http
            ->withHeaders([
                'Authorization' => 'Bot '.$token,
            ])
            ->baseUrl('https://discord.com/api/v10')
            ->acceptJson()
            ->post("/channels/{$channelId}/messages", $payload)
            ->throw();
    }
}
