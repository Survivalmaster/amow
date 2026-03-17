<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
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
            'users' => User::query()->with(['character.faction', 'permissions.accountIcon'])->orderBy('name')->get(),
            'permissions' => Permission::query()->with('accountIcon')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
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

        return back()->with('status', "Updated user {$user->email}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $email = $user->email;
        $user->delete();

        return back()->with('status', "Deleted user {$email}.");
    }
}
