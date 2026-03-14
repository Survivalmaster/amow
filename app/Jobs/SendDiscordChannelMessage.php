<?php

namespace App\Jobs;

use App\Services\Discord\DiscordClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendDiscordChannelMessage implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $channelId,
        public string $content,
    ) {
    }

    public function handle(DiscordClient $discord): void
    {
        $discord->sendMessage($this->channelId, $this->content);
    }
}
