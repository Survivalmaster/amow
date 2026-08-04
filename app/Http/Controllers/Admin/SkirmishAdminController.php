<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skirmish;
use App\Services\Discord\AdminActionLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SkirmishAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.skirmishes', [
            'skirmishes' => Skirmish::query()->latest('starts_at')->latest()->get(),
        ]);
    }

    public function store(Request $request, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $skirmish = Skirmish::query()->create($this->validatedData($request));
        $adminActionLogger->created($request->user(), 'Skirmish', $skirmish);

        return back()->with('status', 'Skirmish created.');
    }

    public function update(Request $request, Skirmish $skirmish, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($skirmish);
        $skirmish->update($this->validatedData($request, $skirmish));
        $adminActionLogger->updated($request->user(), 'Skirmish', $before, $skirmish);

        return back()->with('status', 'Skirmish updated.');
    }

    public function destroy(Request $request, Skirmish $skirmish, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $adminActionLogger->snapshot($skirmish);
        $skirmish->delete();
        $adminActionLogger->deleted($request->user(), 'Skirmish', $snapshot);

        return back()->with('status', 'Skirmish deleted.');
    }

    private function validatedData(Request $request, ?Skirmish $skirmish = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('skirmishes', 'slug')->ignore($skirmish?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['draft', 'open', 'active', 'resolved', 'cancelled'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);
    }
}
