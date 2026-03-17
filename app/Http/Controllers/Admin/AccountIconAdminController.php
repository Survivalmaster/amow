<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountIcon;
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

    public function store(Request $request): RedirectResponse
    {
        AccountIcon::query()->create($this->validatedData($request));

        return back()->with('status', 'Account icon created.');
    }

    public function update(Request $request, AccountIcon $accountIcon): RedirectResponse
    {
        $accountIcon->update($this->validatedData($request, $accountIcon));

        return back()->with('status', 'Account icon updated.');
    }

    public function destroy(AccountIcon $accountIcon): RedirectResponse
    {
        $accountIcon->delete();

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
