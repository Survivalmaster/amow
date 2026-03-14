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
        $token = (string) config('services.discord.bot_token');

        if ($token === '') {
            throw new RuntimeException('Discord bot token is not configured.');
        }

        return $this->http
            ->withToken($token)
            ->baseUrl('https://discord.com/api/v10')
            ->acceptJson()
            ->post("/channels/{$channelId}/messages", [
                'content' => $content,
            ])
            ->throw();
    }
}
