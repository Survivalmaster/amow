<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Services\Discord\AdminActionLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.permissions', [
            'permissions' => Permission::query()->orderBy('sort_order')->orderBy('name')->get(),
            'adminSections' => config('admin_sections'),
        ]);
    }

    public function store(Request $request, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $permission = Permission::query()->create($this->validatedData($request));
        $adminActionLogger->created($request->user(), 'Permission', $permission);

        return back()->with('status', 'Permission created.');
    }

    public function update(Request $request, Permission $permission, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($permission);
        $permission->update($this->validatedData($request, $permission));
        $adminActionLogger->updated($request->user(), 'Permission', $before, $permission);

        return back()->with('status', 'Permission updated.');
    }

    public function destroy(Request $request, Permission $permission, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $adminActionLogger->snapshot($permission);
        $permission->delete();
        $adminActionLogger->deleted($request->user(), 'Permission', $snapshot);

        return back()->with('status', 'Permission deleted.');
    }

    private function validatedData(Request $request, ?Permission $permission = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('permissions', 'slug')->ignore($permission?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'icon_type' => ['nullable', 'in:fontawesome'],
            'icon_value' => ['nullable', 'string', 'max:255'],
            'icon_color' => ['nullable', 'string', 'max:20'],
            'icon_tooltip' => ['nullable', 'string', 'max:255'],
            'grants_admin_access' => ['nullable', 'boolean'],
            'admin_sections' => ['nullable', 'array'],
            'admin_sections.*' => ['string', Rule::in(array_keys(config('admin_sections', [])))],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $validated['grants_admin_access'] = $request->boolean('grants_admin_access');
        $validated['admin_sections'] = $validated['grants_admin_access'] ? array_values($validated['admin_sections'] ?? []) : [];
        $validated['icon_type'] = filled($validated['icon_value'] ?? null) ? 'fontawesome' : null;
        $validated['icon_color'] = $validated['icon_color'] ?? null;
        $validated['icon_tooltip'] = $validated['icon_tooltip'] ?? null;

        return $validated;
    }
}
