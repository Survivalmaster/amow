<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Changelogs</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Major AMOW app and Discord updates.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-5">
        @forelse ($changelogs as $changelog)
            <article class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
                <div class="border-b border-white/10 bg-black/20 px-6 py-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#7ead59]">Version {{ $changelog->version }}</p>
                            <h2 class="mt-1 font-['Teko'] text-4xl uppercase tracking-[0.08em] text-[#f4ecd0]">{{ $changelog->title }}</h2>
                        </div>
                        <time class="rounded-full border border-white/10 bg-black/25 px-3 py-1 text-xs uppercase tracking-[0.18em] text-white/55">
                            {{ $changelog->released_at?->format('d M Y') }}
                        </time>
                    </div>
                    @if ($changelog->summary)
                        <p class="mt-3 max-w-3xl text-sm leading-6 text-white/70">{{ $changelog->summary }}</p>
                    @endif
                </div>

                <div class="grid gap-5 px-6 py-5 lg:grid-cols-[minmax(0,1fr)_18rem]">
                    <div class="space-y-4">
                        @if ($changelog->body)
                            <div class="text-sm leading-6 text-white/70">
                                {!! nl2br(e($changelog->body)) !!}
                            </div>
                        @endif
                    </div>

                    <aside class="space-y-4 rounded-2xl border border-[#7ead59]/20 bg-[#7ead59]/10 p-4">
                        @php($groups = $changelog->groupedFeatures())
                        @forelse (array_filter($groups) as $group => $features)
                            @php($icon = ['Added' => 'fa-plus', 'Edited' => 'fa-pen', 'Removed' => 'fa-minus'][$group])
                            @php($color = ['Added' => '#7ead59', 'Edited' => '#f4d77a', 'Removed' => '#f0b29f'][$group])
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.22em]" style="color: {{ $color }}">
                                    <i class="fa-solid {{ $icon }} mr-1"></i>{{ $group }}
                                </p>
                                <ul class="mt-2 space-y-2 text-sm text-white/72">
                                    @foreach ($features as $feature)
                                        <li class="flex gap-2">
                                            <i class="fa-solid fa-angle-right mt-1 text-xs" style="color: {{ $color }}"></i>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @empty
                            <p class="text-sm text-white/55">No update list was attached to this release.</p>
                        @endforelse
                    </aside>
                </div>
            </article>
        @empty
            <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-8 text-center shadow-2xl shadow-black/30">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-black/25 text-2xl text-white/35">
                    <i class="fa-solid fa-scroll"></i>
                </div>
                <p class="mt-5 font-['Teko'] text-4xl uppercase tracking-[0.12em] text-[#f4ecd0]">Coming Soon</p>
                <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-white/55">Major app and Discord updates will appear here once the first changelog is released.</p>
            </section>
        @endforelse
    </div>
</x-app-layout>
