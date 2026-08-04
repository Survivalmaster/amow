<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('bank.index') }}" class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] {{ $marketplaceSection === 'bank' ? 'border-[#7ead59]/40 bg-[#7ead59]/15 text-[#d7edc7]' : 'border-white/10 bg-white/5 text-white/70' }}">Bank</a>
    <a href="{{ route('store.index') }}" class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] {{ $marketplaceSection === 'store' ? 'border-[#7ead59]/40 bg-[#7ead59]/15 text-[#d7edc7]' : 'border-white/10 bg-white/5 text-white/70' }}">Store</a>
    <a href="{{ route('store.licences') }}" class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] {{ $marketplaceSection === 'licences' ? 'border-[#7ead59]/40 bg-[#7ead59]/15 text-[#d7edc7]' : 'border-white/10 bg-white/5 text-white/70' }}">License Center</a>
    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.03] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/35">
        Player Businesses
        <span class="rounded-full border border-white/10 bg-black/25 px-2 py-0.5 text-[9px] text-white/40">Coming Soon</span>
    </span>
</div>
