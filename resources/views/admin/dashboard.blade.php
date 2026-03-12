<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin Command</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Manage accounts, characters, and world content.</p>
        </div>
    </x-slot>

    @include('admin.partials.nav')

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($stats as $label => $value)
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="text-xs uppercase tracking-[0.24em] text-white/45">{{ ucfirst($label) }}</p>
                <p class="mt-3 font-['Teko'] text-5xl uppercase tracking-[0.1em]">{{ number_format($value) }}</p>
            </div>
        @endforeach
    </div>
</x-app-layout>
