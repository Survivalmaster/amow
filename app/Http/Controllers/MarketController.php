<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\StockHolding;
use App\Support\CharacterActivity;
use App\Support\StockMarketImpact;
use App\Support\StockMarketTicker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MarketController extends Controller
{
    public function __construct(
        private readonly StockMarketTicker $ticker,
        private readonly StockMarketImpact $marketImpact,
    )
    {
    }

    public function index(Request $request): View
    {
        $this->ensureDeveloper($request);

        $this->ticker->fluctuateIfDue();

        $character = $request->user()->character()->with('holdings.company')->firstOrFail();

        return view('market.index', [
            'character' => $character,
            'companies' => Company::query()->orderBy('name')->get(),
        ]);
    }

    public function state(Request $request): JsonResponse
    {
        $this->ensureDeveloper($request);

        $this->ticker->fluctuateIfDue();

        return response()->json([
            'companies' => Company::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Company $company) => [
                    'id' => $company->id,
                    'current_price' => number_format((float) $company->current_price, 2, '.', ''),
                    'formatted_price' => number_format((float) $company->current_price, 2),
                    'last_price_updated_at' => $company->last_price_updated_at?->toIso8601String(),
                ])->values(),
        ]);
    }

    public function buy(Request $request, Company $company): RedirectResponse
    {
        $this->ensureDeveloper($request);

        $this->ticker->fluctuateIfDue();
        $company->refresh();

        $validated = $request->validate([
            'shares' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $character = $request->user()->character()->firstOrFail();
        $cost = (int) round($company->current_price * $validated['shares']);

        if ($character->plastic_credits < $cost) {
            return back()->withErrors(['stocks' => 'Not enough Plastic Credits.']);
        }

        DB::transaction(function () use ($character, $company, $validated, $cost) {
            $company = Company::query()->whereKey($company->id)->lockForUpdate()->firstOrFail();

            $holding = StockHolding::query()->firstOrCreate(
                ['character_id' => $character->id, 'company_id' => $company->id],
                ['shares' => 0, 'average_buy_price' => 0]
            );

            $newShares = $holding->shares + $validated['shares'];

            if ($company->max_shares_per_character !== null && $newShares > $company->max_shares_per_character) {
                throw ValidationException::withMessages([
                    'stocks' => "You can only hold {$company->max_shares_per_character} shares of {$company->name}.",
                ]);
            }

            $newAverage = (($holding->shares * $holding->average_buy_price) + ($validated['shares'] * $company->current_price)) / $newShares;

            $holding->update([
                'shares' => $newShares,
                'average_buy_price' => $newAverage,
            ]);

            $character->decrement('plastic_credits', $cost);
            $impact = $this->marketImpact->applyBuyImpact($company, $validated['shares']);

            CharacterActivity::recordTransaction(
                $character,
                'stock_buy',
                -$cost,
                "Bought {$validated['shares']} shares of {$company->name}.",
                [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'shares' => $validated['shares'],
                    ...$impact,
                ]
            );
        });

        return back()->with('status', 'Shares purchased.');
    }

    public function sell(Request $request, Company $company): RedirectResponse
    {
        $this->ensureDeveloper($request);

        $this->ticker->fluctuateIfDue();
        $company->refresh();

        $validated = $request->validate([
            'shares' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $character = $request->user()->character()->firstOrFail();
        $holding = StockHolding::query()->where([
            'character_id' => $character->id,
            'company_id' => $company->id,
        ])->first();

        if (! $holding || $holding->shares < $validated['shares']) {
            return back()->withErrors(['stocks' => 'You do not own that many shares.']);
        }

        $value = (int) round($company->current_price * $validated['shares']);

        DB::transaction(function () use ($character, $holding, $company, $validated, $value) {
            $company = Company::query()->whereKey($company->id)->lockForUpdate()->firstOrFail();

            $remaining = $holding->shares - $validated['shares'];

            if ($remaining === 0) {
                $holding->delete();
            } else {
                $holding->update(['shares' => $remaining]);
            }

            $character->increment('plastic_credits', $value);
            $impact = $this->marketImpact->applySellImpact($company, $validated['shares']);

            CharacterActivity::recordTransaction(
                $character,
                'stock_sell',
                $value,
                "Sold {$validated['shares']} shares of {$company->name}.",
                [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'shares' => $validated['shares'],
                    ...$impact,
                ]
            );
        });

        return back()->with('status', 'Shares sold.');
    }

    private function ensureDeveloper(Request $request): void
    {
        abort_unless($request->user()?->loadMissing('permissions')->hasPermission('developer'), 403);
    }
}
