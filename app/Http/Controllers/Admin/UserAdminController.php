<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountIcon;
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
            'users' => User::query()->with(['character.faction', 'accountIcons'])->orderBy('name')->get(),
            'accountIcons' => AccountIcon::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'is_admin' => ['nullable', 'boolean'],
            'account_icon_ids' => ['nullable', 'array'],
            'account_icon_ids.*' => ['integer', 'exists:account_icons,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->is_admin = $request->boolean('is_admin');

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $iconIds = collect($validated['account_icon_ids'] ?? [])->map(fn ($id) => (int) $id)->values();
        $adminCrownId = AccountIcon::query()->where('slug', 'admin-crown')->value('id');

        if ($user->is_admin && $adminCrownId) {
            $iconIds = $iconIds->push($adminCrownId)->unique()->values();
        }

        if (! $user->is_admin && $adminCrownId) {
            $iconIds = $iconIds->reject(fn ($id) => $id === (int) $adminCrownId)->values();
        }

        $user->accountIcons()->sync($iconIds->all());

        return back()->with('status', "Updated user {$user->email}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $email = $user->email;
        $user->delete();

        return back()->with('status', "Deleted user {$email}.");
    }
}
