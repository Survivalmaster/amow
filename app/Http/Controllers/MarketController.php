<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\StockHolding;
use App\Support\CharacterActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MarketController extends Controller
{
    public function index(Request $request): View
    {
        $character = $request->user()->character()->with('holdings.company')->firstOrFail();

        return view('market.index', [
            'character' => $character,
            'companies' => Company::query()->orderBy('name')->get(),
        ]);
    }

    public function buy(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'shares' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $character = $request->user()->character()->firstOrFail();
        $cost = (int) round($company->current_price * $validated['shares']);

        if ($character->plastic_credits < $cost) {
            return back()->withErrors(['stocks' => 'Not enough Plastic Credits.']);
        }

        DB::transaction(function () use ($character, $company, $validated, $cost) {
            $holding = StockHolding::query()->firstOrCreate(
                ['character_id' => $character->id, 'company_id' => $company->id],
                ['shares' => 0, 'average_buy_price' => 0]
            );

            $newShares = $holding->shares + $validated['shares'];
            $newAverage = (($holding->shares * $holding->average_buy_price) + ($validated['shares'] * $company->current_price)) / $newShares;

            $holding->update([
                'shares' => $newShares,
                'average_buy_price' => $newAverage,
            ]);

            $character->decrement('plastic_credits', $cost);
            CharacterActivity::recordTransaction($character, 'stock_buy', -$cost, "Bought {$validated['shares']} shares of {$company->name}.");
        });

        return back()->with('status', 'Shares purchased.');
    }

    public function sell(Request $request, Company $company): RedirectResponse
    {
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
            $remaining = $holding->shares - $validated['shares'];

            if ($remaining === 0) {
                $holding->delete();
            } else {
                $holding->update(['shares' => $remaining]);
            }

            $character->increment('plastic_credits', $value);
            CharacterActivity::recordTransaction($character, 'stock_sell', $value, "Sold {$validated['shares']} shares of {$company->name}.");
        });

        return back()->with('status', 'Shares sold.');
    }
}
