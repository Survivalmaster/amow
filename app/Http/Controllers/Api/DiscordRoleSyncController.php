<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiscordRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DiscordRoleSyncController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = config('services.discord.bot_sync_secret') ?: config('services.discord.linking_secret');

        if (! $secret || ! hash_equals($secret, (string) $request->header('X-Discord-Sync-Secret'))) {
            abort(403);
        }

        $validated = $request->validate([
            'guild_id' => ['required', 'string', 'max:255'],
            'roles' => ['required', 'array'],
            'roles.*.id' => ['required', 'string', 'max:255'],
            'roles.*.name' => ['required', 'string', 'max:255'],
            'roles.*.color' => ['nullable', 'string', 'max:20'],
            'roles.*.position' => ['required', 'integer'],
            'roles.*.managed' => ['required', 'boolean'],
            'roles.*.members' => ['present', 'array'],
            'roles.*.members.*.id' => ['required', 'string', 'max:255'],
            'roles.*.members.*.username' => ['nullable', 'string', 'max:255'],
            'roles.*.members.*.display_name' => ['nullable', 'string', 'max:255'],
            'roles.*.members.*.avatar_url' => ['nullable', 'url', 'max:2048'],
            'roles.*.members.*.joined_at' => ['nullable', 'date'],
        ]);

        $roleIds = collect($validated['roles'])->pluck('id');

        if ($roleIds->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'roles' => 'Each Discord role may only be sent once per sync.',
            ]);
        }

        $syncedAt = now();

        DB::transaction(function () use ($validated, $roleIds, $syncedAt): void {
            DiscordRole::query()
                ->whereNotIn('discord_id', $roleIds->all())
                ->delete();

            foreach ($validated['roles'] as $rolePayload) {
                $role = DiscordRole::query()->updateOrCreate(
                    ['discord_id' => $rolePayload['id']],
                    [
                        'name' => $rolePayload['name'],
                        'color' => $rolePayload['color'] ?? null,
                        'position' => $rolePayload['position'],
                        'is_managed' => $rolePayload['managed'],
                        'member_count' => count($rolePayload['members']),
                        'synced_at' => $syncedAt,
                    ]
                );

                $memberIds = collect($rolePayload['members'])->pluck('id')->all();

                $role->members()
                    ->whereNotIn('discord_user_id', $memberIds)
                    ->delete();

                foreach ($rolePayload['members'] as $memberPayload) {
                    $role->members()->updateOrCreate(
                        ['discord_user_id' => $memberPayload['id']],
                        [
                            'username' => $memberPayload['username'] ?? null,
                            'display_name' => $memberPayload['display_name'] ?? null,
                            'avatar_url' => $memberPayload['avatar_url'] ?? null,
                            'joined_at' => isset($memberPayload['joined_at']) ? Carbon::parse($memberPayload['joined_at']) : null,
                            'synced_at' => $syncedAt,
                        ]
                    );
                }
            }
        });

        return response()->json([
            'synced' => true,
            'role_count' => count($validated['roles']),
            'member_assignment_count' => collect($validated['roles'])->sum(fn (array $role): int => count($role['members'])),
        ]);
    }
}
