<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountIcon;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.permissions', [
            'permissions' => Permission::query()->with('accountIcon')->orderBy('sort_order')->orderBy('name')->get(),
            'accountIcons' => AccountIcon::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Permission::query()->create($this->validatedData($request));

        return back()->with('status', 'Permission created.');
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $permission->update($this->validatedData($request, $permission));

        return back()->with('status', 'Permission updated.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return back()->with('status', 'Permission deleted.');
    }

    private function validatedData(Request $request, ?Permission $permission = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('permissions', 'slug')->ignore($permission?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'account_icon_id' => ['nullable', 'integer', 'exists:account_icons,id'],
            'grants_admin_access' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $validated['grants_admin_access'] = $request->boolean('grants_admin_access');
        $validated['account_icon_id'] = $validated['account_icon_id'] ?? null;

        return $validated;
    }
}
