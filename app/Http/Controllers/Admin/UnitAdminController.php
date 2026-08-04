<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Services\Discord\AdminActionLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.units', [
            'units' => Unit::query()->orderBy('category')->orderBy('cost')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $unit = Unit::query()->create($this->validatedData($request));
        $adminActionLogger->created($request->user(), 'Unit', $unit);

        return back()->with('status', 'Unit created.');
    }

    public function update(Request $request, Unit $unit, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($unit);
        $unit->update($this->validatedData($request, $unit));
        $adminActionLogger->updated($request->user(), 'Unit', $before, $unit);

        return back()->with('status', 'Unit updated.');
    }

    public function destroy(Request $request, Unit $unit, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $adminActionLogger->snapshot($unit);
        $unit->delete();
        $adminActionLogger->deleted($request->user(), 'Unit', $snapshot);

        return back()->with('status', 'Unit deleted.');
    }

    private function validatedData(Request $request, ?Unit $unit = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('units', 'slug')->ignore($unit?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'string', 'max:80'],
            'firepower' => ['required', 'integer', 'min:0'],
            'cost' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
