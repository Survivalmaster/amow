<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use App\Services\Discord\AdminActionLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $canManageEmail = $request->user()->loadMissing('permissions')->hasPermission('developer');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [$canManageEmail ? 'required' : 'nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
            'password' => ['nullable', 'string', 'min:8'],
            'ban_reason' => ['nullable', 'string', 'max:2000'],
            'is_banned' => ['nullable', 'boolean'],
        ]);

        $user->name = $validated['name'];
        if ($canManageEmail) {
            $user->email = $validated['email'];
        }
        $isBeingBanned = $request->boolean('is_banned');

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->banned_at = $isBeingBanned ? ($user->banned_at ?? now()) : null;
        $user->banned_reason = $isBeingBanned ? ($validated['ban_reason'] ?? 'No reason provided.') : null;

        $permissionIds = collect($validated['permission_ids'] ?? [])->map(fn ($id) => (int) $id)->values();
        $adminPermissionId = Permission::query()->where('slug', 'admin')->value('id');
        $nationLeaderPermissionId = Permission::query()->where('slug', 'nation-leader')->value('id');

        DB::transaction(function () use ($user, $permissionIds, $adminPermissionId, $nationLeaderPermissionId) {
            $user->is_admin = $adminPermissionId ? $permissionIds->contains((int) $adminPermissionId) : false;
            $user->save();
            $user->permissions()->sync($permissionIds->all());

            if ($nationLeaderPermissionId && $permissionIds->contains((int) $nationLeaderPermissionId) && $user->character) {
                User::query()
                    ->where('id', '!=', $user->id)
                    ->whereHas('character', fn ($query) => $query->where('faction_id', $user->character->faction_id))
                    ->whereHas('permissions', fn ($query) => $query->where('permissions.id', $nationLeaderPermissionId))
                    ->get()
                    ->each(function (User $otherUser) use ($nationLeaderPermissionId) {
                        $otherUser->permissions()->detach($nationLeaderPermissionId);
                        $otherUser->update([
                            'is_admin' => $otherUser->permissions()->where('permissions.slug', 'admin')->exists(),
                        ]);
                    });
            }
        });

        $after = $this->userAuditSnapshot($user->fresh('permissions'));
        $before['password_changed'] = 'false';
        $after['password_changed'] = ! empty($validated['password']) ? 'true' : 'false';
        $adminActionLogger->updated($request->user(), 'User', $before, $after);

        return back()->with('status', $canManageEmail ? "Updated user {$user->email}." : "Updated user #{$user->id}.");
    }

    public function destroy(Request $request, User $user, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $this->userAuditSnapshot($user);
        $canViewPlayerEmails = $request->user()->loadMissing('permissions')->hasPermission('developer');
        $label = $canViewPlayerEmails ? $user->email : "user #{$user->id}";
        $user->delete();
        $adminActionLogger->deleted($request->user(), 'User', $snapshot);

        return back()->with('status', "Deleted {$label}.");
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
