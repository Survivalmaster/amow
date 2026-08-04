<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Stock Market</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Control listed companies, random drift, and player trade impact.</p>
        </div>
    </x-slot>

    @include('admin.partials.nav')

    <div class="space-y-6">
        <section class="rounded-lg border border-slate-800 bg-slate-900/35 p-5">
            <div class="mb-5 flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase text-slate-100">Market Controls</p>
                    <p class="text-sm text-slate-400">Random drift runs on the scheduled ticker. Trade impact applies instantly when players buy or sell.</p>
                </div>
                <p class="text-xs font-semibold uppercase text-slate-500">{{ $companies->count() }} companies listed</p>
            </div>

            <form method="POST" action="{{ route('admin.stock-market.update') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @csrf
                @method('PATCH')

                <label class="grid gap-2 text-sm text-slate-300">
                    <span class="text-xs font-semibold uppercase text-slate-500">Minimum Random Change %</span>
                    <input type="number" step="0.01" name="min_change_percent" value="{{ old('min_change_percent', $settings->min_change_percent) }}" required>
                </label>
                <label class="grid gap-2 text-sm text-slate-300">
                    <span class="text-xs font-semibold uppercase text-slate-500">Maximum Random Change % (10 max)</span>
                    <input type="number" step="0.01" name="max_change_percent" value="{{ old('max_change_percent', $settings->max_change_percent) }}" required>
                </label>
                <label class="grid gap-2 text-sm text-slate-300">
                    <span class="text-xs font-semibold uppercase text-slate-500">Passive Growth Bias %</span>
                    <input type="number" step="0.01" min="0" max="10" name="passive_growth_bias_percent" value="{{ old('passive_growth_bias_percent', $settings->passive_growth_bias_percent) }}" required>
                </label>
                <label class="grid gap-2 text-sm text-slate-300">
                    <span class="text-xs font-semibold uppercase text-slate-500">Low Price Recovery %</span>
                    <input type="number" step="0.01" min="0" max="35" name="low_price_recovery_percent" value="{{ old('low_price_recovery_percent', $settings->low_price_recovery_percent) }}" required>
                </label>
                <label class="grid gap-2 text-sm text-slate-300">
                    <span class="text-xs font-semibold uppercase text-slate-500">Low Price Minimum Lift</span>
                    <input type="number" step="0.01" min="0" max="10" name="low_price_minimum_lift" value="{{ old('low_price_minimum_lift', $settings->low_price_minimum_lift) }}" required>
                </label>
                <label class="grid gap-2 text-sm text-slate-300">
                    <span class="text-xs font-semibold uppercase text-slate-500">Buy Impact / 100 Shares %</span>
                    <input type="number" step="0.01" min="0" name="buy_impact_percent_per_100_shares" value="{{ old('buy_impact_percent_per_100_shares', $settings->buy_impact_percent_per_100_shares) }}" required>
                </label>
                <label class="grid gap-2 text-sm text-slate-300">
                    <span class="text-xs font-semibold uppercase text-slate-500">Sell Impact / 100 Shares %</span>
                    <input type="number" step="0.01" min="0" name="sell_impact_percent_per_100_shares" value="{{ old('sell_impact_percent_per_100_shares', $settings->sell_impact_percent_per_100_shares) }}" required>
                </label>
                <label class="grid gap-2 text-sm text-slate-300">
                    <span class="text-xs font-semibold uppercase text-slate-500">Max Trade Impact %</span>
                    <input type="number" step="0.01" min="0" name="max_trade_impact_percent" value="{{ old('max_trade_impact_percent', $settings->max_trade_impact_percent) }}" required>
                </label>
                <label class="grid gap-2 text-sm text-slate-300">
                    <span class="text-xs font-semibold uppercase text-slate-500">Crash Threshold Shares</span>
                    <input type="number" min="1" name="crash_trade_threshold_shares" value="{{ old('crash_trade_threshold_shares', $settings->crash_trade_threshold_shares) }}" required>
                </label>
                <label class="grid gap-2 text-sm text-slate-300">
                    <span class="text-xs font-semibold uppercase text-slate-500">Crash Extra %</span>
                    <input type="number" step="0.01" min="0" name="crash_extra_percent" value="{{ old('crash_extra_percent', $settings->crash_extra_percent) }}" required>
                </label>
                <div class="flex items-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-3 text-xs font-semibold uppercase text-white">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Settings
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-lg border border-slate-800 bg-slate-900/35 p-5">
            <div class="mb-5">
                <p class="font-['Teko'] text-3xl uppercase text-slate-100">List A Company</p>
                <p class="text-sm text-slate-400">Create a new stock-market entry players can buy and sell.</p>
            </div>

            <form method="POST" action="{{ route('admin.stock-market.companies.store') }}" class="grid gap-4 xl:grid-cols-[1fr_12rem]">
                @csrf
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="grid gap-2 text-sm text-slate-300">
                        <span class="text-xs font-semibold uppercase text-slate-500">Company Name</span>
                        <input name="name" value="{{ old('name') }}" required>
                    </label>
                    <label class="grid gap-2 text-sm text-slate-300">
                        <span class="text-xs font-semibold uppercase text-slate-500">Starting Price</span>
                        <input type="number" step="0.01" min="0.01" name="current_price" value="{{ old('current_price', 25) }}" required>
                    </label>
                    <label class="grid gap-2 text-sm text-slate-300">
                        <span class="text-xs font-semibold uppercase text-slate-500">Max Shares / Character</span>
                        <input type="number" min="1" name="max_shares_per_character" value="{{ old('max_shares_per_character', 1000) }}" placeholder="Blank for no cap">
                    </label>
                    <label class="grid gap-2 text-sm text-slate-300 md:col-span-3">
                        <span class="text-xs font-semibold uppercase text-slate-500">Description</span>
                        <textarea name="description" rows="3" required>{{ old('description') }}</textarea>
                    </label>
                </div>
                <div class="flex items-end">
                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-3 text-xs font-semibold uppercase text-white">
                        <i class="fa-solid fa-plus"></i>
                        Add Company
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-lg border border-slate-800 bg-slate-900/35 p-5">
            <p class="font-['Teko'] text-3xl uppercase text-slate-100">Listed Companies</p>
            <div class="mt-5 space-y-4">
                @forelse ($companies as $company)
                    <div class="rounded-lg border border-slate-800 bg-slate-950/40 p-4">
                        <form method="POST" action="{{ route('admin.stock-market.companies.update', $company) }}" class="grid gap-4 xl:grid-cols-[1fr_11rem_9rem]">
                            @csrf
                            @method('PATCH')
                            <div class="grid gap-4 md:grid-cols-3">
                                <label class="grid gap-2 text-sm text-slate-300">
                                    <span class="text-xs font-semibold uppercase text-slate-500">Name</span>
                                    <input name="name" value="{{ old("companies.{$company->id}.name", $company->name) }}" required>
                                </label>
                                <label class="grid gap-2 text-sm text-slate-300">
                                    <span class="text-xs font-semibold uppercase text-slate-500">Current Price</span>
                                    <input type="number" step="0.01" min="0.01" name="current_price" value="{{ old("companies.{$company->id}.current_price", $company->current_price) }}" required>
                                </label>
                                <label class="grid gap-2 text-sm text-slate-300">
                                    <span class="text-xs font-semibold uppercase text-slate-500">Max Shares / Character</span>
                                    <input type="number" min="1" name="max_shares_per_character" value="{{ old("companies.{$company->id}.max_shares_per_character", $company->max_shares_per_character) }}" placeholder="Blank for no cap">
                                </label>
                                <label class="grid gap-2 text-sm text-slate-300 md:col-span-3">
                                    <span class="text-xs font-semibold uppercase text-slate-500">Description</span>
                                    <textarea name="description" rows="2" required>{{ old("companies.{$company->id}.description", $company->description) }}</textarea>
                                </label>
                            </div>
                            <div class="grid content-end gap-2 text-xs uppercase text-slate-500">
                                <span>{{ number_format($company->holdings_count) }} holders</span>
                                <span>{{ $company->max_shares_per_character ? number_format($company->max_shares_per_character).' max shares' : 'No share cap' }}</span>
                                <span>Updated {{ optional($company->last_price_updated_at)->format('d M H:i') ?? 'never' }}</span>
                            </div>
                            <div class="flex items-end gap-2">
                                <button class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-3 text-xs font-semibold uppercase text-white">
                                    <i class="fa-solid fa-check"></i>
                                    Save
                                </button>
                            </div>
                        </form>
                        <div class="mt-3 flex flex-wrap justify-end gap-2">
                            <form method="POST" action="{{ route('admin.stock-market.companies.crash', $company) }}" onsubmit="return confirm('Crash {{ addslashes($company->name) }} now? This will immediately slash the live share price.');">
                                @csrf
                                <button class="inline-flex items-center gap-2 rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-2 text-xs font-semibold uppercase text-red-100">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    CRASH
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.stock-market.companies.destroy', $company) }}">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center gap-2 rounded-lg border border-red-500/30 px-4 py-2 text-xs font-semibold uppercase text-red-200" @disabled($company->holdings_count > 0)>
                                    <i class="fa-solid fa-trash"></i>
                                    {{ $company->holdings_count > 0 ? 'Cannot delete with holders' : 'Delete Company' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-800 p-8 text-center text-sm uppercase text-slate-500">
                        No companies listed yet.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
