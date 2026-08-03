<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\Rank;
use App\Services\Discord\AdminActionLogger;
use App\Support\CharacterActivity;
use App\Support\DiscordCharacterRankSynchronizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CharacterAdminController extends Controller
{
    public function index(DiscordCharacterRankSynchronizer $rankSynchronizer): View
    {
        $rankSynchronizer->syncLinkedCharacters();

        return view('admin.characters', [
            'characters' => Character::query()->with(['user', 'faction', 'rank', 'currentJob'])->orderBy('name')->get(),
            'factions' => Faction::query()->orderBy('name')->get(),
            'jobs' => GameJob::query()->orderBy('required_level')->orderBy('name')->get(),
            'ranks' => Rank::query()->orderBy('order_index')->get(),
            'militaryRanks' => Rank::query()->where('is_military', true)->orderBy('order_index')->get(),
        ]);
    }

    public function update(Request $request, Character $character, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($character);
        $beforeAttributes = $character->attributesToArray();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'between:16,80'],
            'faction_id' => ['required', 'exists:factions,id'],
            'rank_id' => ['required', 'exists:ranks,id'],
            'starting_occupation' => ['required', 'in:Laborer,Merchant,Mechanic'],
            'current_job_id' => ['nullable', 'exists:game_jobs,id'],
            'role_type' => ['required', 'in:civilian,military'],
            'plastic_credits' => ['required', 'integer', 'min:0'],
            'influence_score' => ['required', 'integer', 'min:0'],
            'military_score' => ['required', 'integer', 'min:0'],
            'economic_score' => ['required', 'integer', 'min:0'],
            'level' => ['required', 'integer', 'min:0'],
            'experience_points' => ['required', 'integer', 'min:0'],
            'health_points' => ['required', 'integer', 'min:0', 'max:100'],
            'stamina_points' => ['required', 'integer', 'min:0', 'max:100'],
            'armor_points' => ['required', 'integer', 'min:0', 'max:100'],
            'is_business_owner' => ['nullable', 'boolean'],
            'is_nation_leader' => ['nullable', 'boolean'],
            'job_changed_at' => ['nullable', 'date'],
            'biography' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $character, $validated) {
            $isNationLeader = $request->boolean('is_nation_leader');

            if ($isNationLeader) {
                Character::query()
                    ->where('faction_id', $validated['faction_id'])
                    ->where('id', '!=', $character->id)
                    ->update(['is_nation_leader' => false]);
            }

            $character->update([
                ...$validated,
                'is_business_owner' => $request->boolean('is_business_owner'),
                'is_nation_leader' => $isNationLeader,
            ]);
        });

        $adminActionLogger->updated($request->user(), 'Character', $before, $character);
        $this->recordCharacterAdminUpdate($request, $character, $beforeAttributes);

        return back()->with('status', "Updated character {$character->name}.");
    }

    public function destroy(Request $request, Character $character, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $adminActionLogger->snapshot($character);
        $name = $character->name;
        $character->delete();
        $adminActionLogger->deleted($request->user(), 'Character', $snapshot);

        return back()->with('status', "Deleted character {$name}.");
    }

    private function recordCharacterAdminUpdate(Request $request, Character $character, array $before): void
    {
        $after = $character->fresh()->attributesToArray();
        $ignoredKeys = ['updated_at'];
        $changedFields = [];
        $adminLabel = $request->user()->name ?: 'Admin #'.$request->user()->id;

        foreach (array_keys($before + $after) as $key) {
            if (in_array($key, $ignoredKeys, true)) {
                continue;
            }

            if (($before[$key] ?? null) === ($after[$key] ?? null)) {
                continue;
            }

            $changedFields[] = $key;
        }

        if ($changedFields === []) {
            return;
        }

        if (in_array('rank_id', $changedFields, true)) {
            CharacterActivity::recordTransaction(
                $character,
                'rank_change',
                0,
                'Rank changed by '.$adminLabel.'.',
                [
                    'from_rank' => Rank::query()->find($before['rank_id'] ?? null)?->name,
                    'to_rank' => Rank::query()->find($after['rank_id'] ?? null)?->name,
                    'changed_by' => $adminLabel,
                ]
            );
        }

        if (in_array('current_job_id', $changedFields, true)) {
            CharacterActivity::recordTransaction(
                $character,
                'job_change',
                0,
                'Job changed by '.$adminLabel.'.',
                [
                    'from_job' => GameJob::query()->find($before['current_job_id'] ?? null)?->name,
                    'to_job' => GameJob::query()->find($after['current_job_id'] ?? null)?->name,
                    'changed_by' => $adminLabel,
                ]
            );
        }

        CharacterActivity::recordTransaction(
            $character,
            'admin_update',
            0,
            'Character updated by '.$adminLabel.'.',
            [
                'admin' => $adminLabel,
                'changed_fields' => implode(', ', $changedFields),
            ]
        );
    }
}
