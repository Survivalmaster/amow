<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountIcon;
use App\Services\Discord\AdminActionLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountIconAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.account-icons', [
            'accountIcons' => AccountIcon::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $accountIcon = AccountIcon::query()->create($this->validatedData($request));
        $adminActionLogger->created($request->user(), 'Account Icon', $accountIcon);

        return back()->with('status', 'Account icon created.');
    }

    public function update(Request $request, AccountIcon $accountIcon, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($accountIcon);
        $accountIcon->update($this->validatedData($request, $accountIcon));
        $adminActionLogger->updated($request->user(), 'Account Icon', $before, $accountIcon);

        return back()->with('status', 'Account icon updated.');
    }

    public function destroy(Request $request, AccountIcon $accountIcon, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $adminActionLogger->snapshot($accountIcon);
        $accountIcon->delete();
        $adminActionLogger->deleted($request->user(), 'Account Icon', $snapshot);

        return back()->with('status', 'Account icon deleted.');
    }

    private function validatedData(Request $request, ?AccountIcon $accountIcon = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:account_icons,slug,'.($accountIcon?->id ?? 'NULL')],
            'icon_type' => ['required', 'in:fontawesome'],
            'icon_value' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:20'],
            'tooltip' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);
    }
}
