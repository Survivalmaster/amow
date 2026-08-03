<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameJob;
use App\Services\Discord\AdminActionLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameJobAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.jobs', [
            'jobs' => GameJob::query()->withCount('characters')->orderBy('required_level')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $validated = $this->validatedData($request);

        if ($validated['is_starter']) {
            GameJob::query()->update(['is_starter' => false]);
        }

        $gameJob = GameJob::query()->create($validated);
        $adminActionLogger->created($request->user(), 'Job', $gameJob);

        return back()->with('status', 'Job created.');
    }

    public function update(Request $request, GameJob $gameJob, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($gameJob);
        $validated = $this->validatedData($request, $gameJob);

        if ($validated['is_starter']) {
            GameJob::query()->whereKeyNot($gameJob->id)->update(['is_starter' => false]);
        }

        $gameJob->update($validated);
        $adminActionLogger->updated($request->user(), 'Job', $before, $gameJob);

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
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:game_jobs,slug,'.($gameJob?->id ?? 'NULL')],
            'description' => ['nullable', 'string', 'max:1000'],
            'min_pay' => ['required', 'integer', 'min:0'],
            'max_pay' => ['required', 'integer', 'gte:min_pay'],
            'required_level' => ['required', 'integer', 'min:0'],
            'work_cooldown_minutes' => ['required', 'integer', 'min:1'],
            'stamina_decrease' => ['required', 'integer', 'min:0', 'max:100'],
            'experience_reward' => ['required', 'integer', 'min:0'],
            'working_display_message' => ['nullable', 'string', 'max:255'],
            'is_starter' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'is_starter' => $request->boolean('is_starter'),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
