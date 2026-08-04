<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\StockMarketSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockMarketAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.stock-market', [
            'settings' => StockMarketSetting::query()->firstOrCreate(
                ['id' => 1],
                [
                    'min_change_percent' => -3,
                    'max_change_percent' => 3,
                    'buy_impact_percent_per_100_shares' => 0.35,
                    'sell_impact_percent_per_100_shares' => 0.45,
                    'max_trade_impact_percent' => 12,
                    'crash_trade_threshold_shares' => 100,
                    'crash_extra_percent' => 4,
                ]
            ),
            'companies' => Company::query()->withCount('holdings')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'min_change_percent' => ['required', 'numeric', 'between:-99,99'],
            'max_change_percent' => ['required', 'numeric', 'between:-99,99'],
            'buy_impact_percent_per_100_shares' => ['required', 'numeric', 'between:0,99'],
            'sell_impact_percent_per_100_shares' => ['required', 'numeric', 'between:0,99'],
            'max_trade_impact_percent' => ['required', 'numeric', 'between:0,99'],
            'crash_trade_threshold_shares' => ['required', 'integer', 'min:1', 'max:1000000'],
            'crash_extra_percent' => ['required', 'numeric', 'between:0,99'],
        ]);

        if ((float) $validated['min_change_percent'] > (float) $validated['max_change_percent']) {
            return back()->withErrors(['min_change_percent' => 'The minimum change cannot be greater than the maximum change.']);
        }

        StockMarketSetting::query()->updateOrCreate(
            ['id' => 1],
            $validated
        );

        return back()->with('status', 'Stock market settings updated.');
    }

    public function storeCompany(Request $request): RedirectResponse
    {
        $validated = $this->validateCompany($request);
        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated['last_price_updated_at'] = now();

        Company::query()->create($validated);

        return back()->with('status', 'Company listed on the stock market.');
    }

    public function updateCompany(Request $request, Company $company): RedirectResponse
    {
        $validated = $this->validateCompany($request, $company);
        $validated['slug'] = $this->uniqueSlug($validated['name'], $company);

        $company->update($validated);

        return back()->with('status', 'Company listing updated.');
    }

    public function destroyCompany(Company $company): RedirectResponse
    {
        if ($company->holdings()->exists()) {
            return back()->withErrors(['company' => 'This company cannot be deleted while players own shares.']);
        }

        $company->delete();

        return back()->with('status', 'Company removed from the stock market.');
    }

    private function validateCompany(Request $request, ?Company $company = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('companies', 'name')->ignore($company)],
            'current_price' => ['required', 'numeric', 'min:5', 'max:99999999.99'],
            'description' => ['required', 'string', 'max:2000'],
        ]);
    }

    private function uniqueSlug(string $name, ?Company $company = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (Company::query()
            ->where('slug', $slug)
            ->when($company, fn ($query) => $query->whereKeyNot($company->id))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
