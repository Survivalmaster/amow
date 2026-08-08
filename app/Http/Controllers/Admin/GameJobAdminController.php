<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameJob;
use App\Models\Item;
use App\Services\Discord\AdminActionLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GameJobAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.jobs', [
            'jobs' => GameJob::query()->with('drops.item')->withCount('characters')->orderBy('required_level')->orderBy('name')->get(),
            'dropItems' => Item::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $validated = $this->validatedData($request);

        if ($validated['is_starter']) {
            GameJob::query()->update(['is_starter' => false]);
        }

        DB::transaction(function () use ($request, $validated, $adminActionLogger) {
            $gameJob = GameJob::query()->create($validated);
            $this->syncDropRules($request, $gameJob);
            $adminActionLogger->created($request->user(), 'Job', $gameJob);
        });

        return back()->with('status', 'Job created.');
    }

    public function update(Request $request, GameJob $gameJob, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($gameJob);
        $validated = $this->validatedData($request, $gameJob);

        if ($validated['is_starter']) {
            GameJob::query()->whereKeyNot($gameJob->id)->update(['is_starter' => false]);
        }

        DB::transaction(function () use ($request, $gameJob, $validated, $adminActionLogger, $before) {
            $gameJob->update($validated);
            $this->syncDropRules($request, $gameJob);
            $adminActionLogger->updated($request->user(), 'Job', $before, $gameJob);
        });

        return back()->with('status', 'Job updated.');
    }

    public function destroy(Request $request, GameJob $gameJob, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        if ($gameJob->characters()->exists()) {
            return back()->withErrors(['jobs' => 'This job is assigned to one or more characters.']);
        }

        $snapshot = $adminActionLogger->snapshot($gameJob);
        $gameJob->delete();
        $adminActionLogger->deleted($request->user(), 'Job', $snapshot);

        return back()->with('status', 'Job deleted.');
    }

    private function validatedData(Request $request, ?GameJob $gameJob = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:game_jobs,slug,'.($gameJob?->id ?? 'NULL')],
            'description' => ['nullable', 'string', 'max:1000'],
            'min_pay' => ['required', 'integer', 'min:0'],
            'max_pay' => ['required', 'integer', 'gte:min_pay'],
            'required_level' => ['required', 'integer', 'min:0'],
            'work_cooldown_minutes' => ['required', 'integer', 'min:1'],
            'stamina_decrease' => ['required', 'integer', 'min:0', 'max:100'],
            'experience_reward' => ['required', 'integer', 'min:0'],
            'max_tier' => ['nullable', 'integer', 'min:1', 'max:20'],
            'tier_xp_required' => ['nullable', 'integer', 'min:1'],
            'tier_pay_bonus_percent' => ['nullable', 'integer', 'min:0', 'max:500'],
            'tier_xp_bonus_percent' => ['nullable', 'integer', 'min:0', 'max:500'],
            'working_display_message' => ['nullable', 'string', 'max:255'],
            'drop_rules_text' => ['nullable', 'string', 'max:6000'],
            'is_starter' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'is_starter' => $request->boolean('is_starter'),
            'is_active' => $request->boolean('is_active'),
        ];

        unset($validated['drop_rules_text']);

        $validated['max_tier'] = (int) ($validated['max_tier'] ?? $gameJob?->max_tier ?? 20);
        $validated['tier_xp_required'] = (int) ($validated['tier_xp_required'] ?? $gameJob?->tier_xp_required ?? 100);
        $validated['tier_pay_bonus_percent'] = (int) ($validated['tier_pay_bonus_percent'] ?? $gameJob?->tier_pay_bonus_percent ?? 5);
        $validated['tier_xp_bonus_percent'] = (int) ($validated['tier_xp_bonus_percent'] ?? $gameJob?->tier_xp_bonus_percent ?? 5);

        return $validated;
    }

    private function syncDropRules(Request $request, GameJob $gameJob): void
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', (string) $request->input('drop_rules_text', '')))
            ->map(fn (string $line) => trim($line))
            ->filter();

        $gameJob->drops()->delete();

        foreach ($lines as $lineNumber => $line) {
            $parts = array_map('trim', preg_split('/\s*\|\s*/', $line) ?: []);

            if (count($parts) !== 6) {
                throw ValidationException::withMessages([
                    'drop_rules_text' => 'Drop rule line '.($lineNumber + 1).' must use: item-slug | min tier | max tier | min qty | max qty | chance %',
                ]);
            }

            [$itemSlug, $minTier, $maxTier, $minQuantity, $maxQuantity, $chance] = $parts;
            $item = Item::query()->where('slug', $itemSlug)->first();
            $normalizedMinTier = max(1, min(20, (int) $minTier));
            $normalizedMaxTier = max($normalizedMinTier, min(20, (int) $maxTier));
            $normalizedMinQuantity = max(1, (int) $minQuantity);

            if (! $item) {
                throw ValidationException::withMessages([
                    'drop_rules_text' => 'Drop rule line '.($lineNumber + 1)." references an unknown item slug: {$itemSlug}.",
                ]);
            }

            $gameJob->drops()->create([
                'item_id' => $item->id,
                'min_tier' => $normalizedMinTier,
                'max_tier' => $normalizedMaxTier,
                'min_quantity' => $normalizedMinQuantity,
                'max_quantity' => max($normalizedMinQuantity, (int) $maxQuantity),
                'drop_chance_percent' => max(0, min(100, (float) $chance)),
            ]);
        }
    }
}
