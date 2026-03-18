<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Moderator</p></x-slot>

    @include('admin.partials.nav')

    <section class="rounded-[2rem] border border-dashed border-white/10 bg-white/[0.03] p-6 shadow-2xl shadow-black/20">
        <p class="font-['Teko'] text-4xl uppercase tracking-[0.08em]">Moderator Tools</p>
        <p class="mt-3 max-w-2xl text-sm leading-7 text-white/65">This page is intentionally blank for now so we can verify moderator-specific navigation and permissions.</p>
    </section>
</x-app-layout>
