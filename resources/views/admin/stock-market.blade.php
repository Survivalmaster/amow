<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Stock Market</p></x-slot>

    @include('admin.partials.nav')

    <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
        <div class="mb-5">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-white">Price Change Controls</p>
            <p class="text-sm text-white/55">Adjust the random stock swing percentages without redeploying the app.</p>
        </div>

        <form method="POST" action="{{ route('admin.stock-market.update') }}" class="grid gap-4 md:grid-cols-2">
            @csrf
            @method('PATCH')
            <label class="grid gap-2 text-sm text-white/70">
                <span class="uppercase tracking-[0.18em] text-white/45">Minimum Change %</span>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" step="0.01" name="min_change_percent" value="{{ $settings->min_change_percent }}" required>
            </label>
            <label class="grid gap-2 text-sm text-white/70">
                <span class="uppercase tracking-[0.18em] text-white/45">Maximum Change %</span>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" step="0.01" name="max_change_percent" value="{{ $settings->max_change_percent }}" required>
            </label>
            <div class="flex items-end md:col-span-2">
                <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save Settings</button>
            </div>
        </form>
    </section>
</x-app-layout>
