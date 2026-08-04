<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Licence;
use App\Services\Discord\AdminActionLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LicenceAdminController extends Controller
{
    public function store(Request $request, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $licence = Licence::query()->create($this->validatedData($request));
        $adminActionLogger->created($request->user(), 'Licence', $licence);

        return back()->with('status', 'Licence created.');
    }

    public function update(Request $request, Licence $licence, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($licence);

        $licence->update($this->validatedData($request, $licence));
        $adminActionLogger->updated($request->user(), 'Licence', $before, $licence);

        return back()->with('status', 'Licence updated.');
    }

    public function destroy(Request $request, Licence $licence, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $adminActionLogger->snapshot($licence);

        try {
            $licence->delete();
        } catch (QueryException) {
            return back()->withErrors('Licence could not be deleted because related records still exist.');
        }

        $adminActionLogger->deleted($request->user(), 'Licence', $snapshot);

        return back()->with('status', 'Licence deleted.');
    }

    protected function validatedData(Request $request, ?Licence $licence = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('licences', 'slug')->ignore($licence?->id)],
            'description' => ['required', 'string'],
            'cost' => ['required', 'integer', 'min:1'],
            'required_level' => ['nullable', 'integer', 'min:0'],
            'grants_business_creation' => ['nullable', 'boolean'],
        ]);

        $validated['grants_business_creation'] = $request->boolean('grants_business_creation');
        $validated['required_rank_id'] = null;

        return $validated;
    }
}
