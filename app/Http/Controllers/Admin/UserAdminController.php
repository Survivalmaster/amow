<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use App\Services\Discord\AdminActionLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.users', [
            'users' => User::query()->with(['character.faction', 'permissions'])->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $this->userAuditSnapshot($user);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $permissionIds = collect($validated['permission_ids'] ?? [])->map(fn ($id) => (int) $id)->values();
        $adminPermissionId = Permission::query()->where('slug', 'admin')->value('id');
        $user->is_admin = $adminPermissionId ? $permissionIds->contains((int) $adminPermissionId) : false;
        $user->save();
        $user->permissions()->sync($permissionIds->all());

        $after = $this->userAuditSnapshot($user->fresh('permissions'));
        $before['password_changed'] = 'false';
        $after['password_changed'] = ! empty($validated['password']) ? 'true' : 'false';
        $adminActionLogger->updated($request->user(), 'User', $before, $after);

        return back()->with('status', "Updated user {$user->email}.");
    }

    public function destroy(Request $request, User $user, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $this->userAuditSnapshot($user);
        $email = $user->email;
        $user->delete();
        $adminActionLogger->deleted($request->user(), 'User', $snapshot);

        return back()->with('status', "Deleted user {$email}.");
    }

    private function userAuditSnapshot(User $user): array
    {
        $user->loadMissing('permissions');

        return [
            ...$user->attributesToArray(),
            'permissions' => $user->permissions->pluck('name')->values()->all(),
        ];
    }
}
