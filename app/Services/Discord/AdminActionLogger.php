<?php

namespace App\Services\Discord;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AdminActionLogger
{
    private const CHANNEL_ID = '1483335218944282685';
    private const ACTOR_LABEL = 'Survivalmaster';

    private const ACTION_COLORS = [
        'created' => '7EAD59',
        'updated' => 'C2A84F',
        'deleted' => 'C65B3F',
    ];

    private const SENSITIVE_KEYS = [
        'password',
        'remember_token',
        'discord_link_token',
        'webhook_url',
    ];

    public function __construct(
        private readonly DiscordClient $discord,
    ) {
    }

    public function snapshot(Model|array $subject): array
    {
        $snapshot = is_array($subject)
            ? $subject
            : $subject->attributesToArray();

        return $this->sanitizeSnapshot($snapshot);
    }

    public function created(User $actor, string $resource, Model|array $subject): void
    {
        $snapshot = $this->snapshot($subject);

        $this->send($actor, 'created', $resource, $snapshot, [
            [
                'name' => 'Created Record',
                'value' => $this->formatCodeBlock($snapshot),
                'inline' => false,
            ],
        ]);
    }

    public function updated(User $actor, string $resource, Model|array $before, Model|array $after): void
    {
        $beforeSnapshot = $this->snapshot($before);
        $afterSnapshot = $this->snapshot($after);
        $changes = $this->diffSnapshots($beforeSnapshot, $afterSnapshot);

        if ($changes === []) {
            return;
        }

        $this->send($actor, 'updated', $resource, $afterSnapshot, [
            [
                'name' => 'Changed Fields',
                'value' => $this->formatCodeBlock($changes),
                'inline' => false,
            ],
            [
                'name' => 'Current Record',
                'value' => $this->formatCodeBlock($afterSnapshot),
                'inline' => false,
            ],
        ]);
    }

    public function deleted(User $actor, string $resource, Model|array $subject): void
    {
        $snapshot = $this->snapshot($subject);

        $this->send($actor, 'deleted', $resource, $snapshot, [
            [
                'name' => 'Deleted Record',
                'value' => $this->formatCodeBlock($snapshot),
                'inline' => false,
            ],
        ]);
    }

    private function send(User $actor, string $action, string $resource, array $snapshot, array $fields): void
    {
        try {
            $this->discord->sendEmbedMessage(self::CHANNEL_ID, [
                'author' => array_filter([
                    'name' => self::ACTOR_LABEL,
                    'icon_url' => $actor->discord_avatar_url,
                ]),
                'title' => sprintf('%s %s', $resource, Str::headline($action)),
                'description' => sprintf(
                    '**%s** was %s by **%s**.',
                    $this->resolveRecordLabel($snapshot),
                    $action,
                    self::ACTOR_LABEL
                ),
                'color' => hexdec(self::ACTION_COLORS[$action] ?? self::ACTION_COLORS['updated']),
                'fields' => $fields,
                'footer' => [
                    'text' => 'AMOW Admin Audit Log',
                ],
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Admin action Discord message failed to send.', [
                'resource' => $resource,
                'action' => $action,
                'actor_id' => $actor->id,
                'channel_id' => self::CHANNEL_ID,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function resolveRecordLabel(array $snapshot): string
    {
        foreach (['name', 'character_name', 'email', 'slug', 'id'] as $key) {
            if (filled($snapshot[$key] ?? null)) {
                return (string) $snapshot[$key];
            }
        }

        return 'record';
    }

    private function diffSnapshots(array $before, array $after): array
    {
        $changes = [];
        $ignoredKeys = ['updated_at'];

        foreach (array_keys($before + $after) as $key) {
            if (in_array($key, $ignoredKeys, true)) {
                continue;
            }

            $beforeValue = $before[$key] ?? null;
            $afterValue = $after[$key] ?? null;

            if ($beforeValue === $afterValue) {
                continue;
            }

            $changes[$key] = [
                'before' => $beforeValue,
                'after' => $afterValue,
            ];
        }

        ksort($changes);

        return $changes;
    }

    private function sanitizeSnapshot(array $snapshot): array
    {
        $sanitized = [];

        foreach ($snapshot as $key => $value) {
            $normalizedKey = Str::lower((string) $key);

            if (in_array($normalizedKey, self::SENSITIVE_KEYS, true)) {
                $sanitized[$key] = '[redacted]';
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeSnapshot($value);
                continue;
            }

            if (is_bool($value)) {
                $sanitized[$key] = $value ? 'true' : 'false';
                continue;
            }

            $sanitized[$key] = is_string($value) ? Str::limit($value, 250) : $value;
        }

        ksort($sanitized);

        return $sanitized;
    }

    private function formatCodeBlock(array $payload): string
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            $json = '{"error":"Unable to encode payload."}';
        }

        if (Str::length($json) > 1010) {
            $json = Str::limit($json, 1007, '...');
        }

        return "```json\n{$json}\n```";
    }
}
