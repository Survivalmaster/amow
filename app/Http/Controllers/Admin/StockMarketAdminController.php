<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMarketSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMarketAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.stock-market', [
            'settings' => StockMarketSetting::query()->firstOrCreate(
                ['id' => 1],
                ['min_change_percent' => -3, 'max_change_percent' => 3]
            ),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'min_change_percent' => ['required', 'numeric', 'between:-99,99'],
            'max_change_percent' => ['required', 'numeric', 'between:-99,99'],
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
}
