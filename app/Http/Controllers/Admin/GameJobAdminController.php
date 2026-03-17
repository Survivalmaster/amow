<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameJobAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.jobs', [
            'jobs' => GameJob::query()->orderBy('required_level')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        if ($validated['is_starter']) {
            GameJob::query()->update(['is_starter' => false]);
        }

        GameJob::query()->create($validated);

        return back()->with('status', 'Job created.');
    }

    public function update(Request $request, GameJob $gameJob): RedirectResponse
    {
        $validated = $this->validatedData($request, $gameJob);

        if ($validated['is_starter']) {
            GameJob::query()->whereKeyNot($gameJob->id)->update(['is_starter' => false]);
        }

        $gameJob->update($validated);

        return back()->with('status', 'Job updated.');
    }

    public function destroy(GameJob $gameJob): RedirectResponse
    {
        if ($gameJob->characters()->exists()) {
            return back()->withErrors(['jobs' => 'This job is assigned to one or more characters.']);
        }

        $gameJob->delete();

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
            'is_starter' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'is_starter' => $request->boolean('is_starter'),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
