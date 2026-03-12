<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Army Men of War Roleplay App</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=rajdhani:500,600,700|teko:500,600,700&display=swap" rel="stylesheet" />
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="min-h-screen bg-[#07100c] font-sans text-[#f4ecd0]">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(126,173,89,0.18),_transparent_26%),linear-gradient(180deg,_rgba(7,16,12,0.5),_rgba(2,6,4,0.95)),linear-gradient(180deg,_#0f1b15_0%,_#07100c_60%,_#030705_100%)]">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <header class="rounded-[2rem] border border-white/10 bg-white/5 px-6 py-5 shadow-2xl shadow-black/30 backdrop-blur">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-3xl border border-[#7ead59]/40 bg-[#7ead59]/15 font-['Teko'] text-4xl tracking-[0.2em] text-[#7ead59]">AM</div>
                            <div>
                                <p class="font-['Teko'] text-4xl uppercase tracking-[0.2em]">Army Men of War</p>
                                <p class="text-sm uppercase tracking-[0.25em] text-white/55">Text roleplay. Politics. Trade. Military life.</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Enter Game</a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-white/15 bg-white/5 px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em]">Login</a>
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Register</a>
                            @endauth
                        </div>
                    </div>
                </header>

                <main class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <section class="rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-2xl shadow-black/30 backdrop-blur">
                        <p class="font-['Teko'] text-xl uppercase tracking-[0.24em] text-[#c2a84f]">Welcome to the World of Army Men of War</p>
                        <h1 class="mt-3 max-w-3xl font-['Teko'] text-6xl uppercase leading-none tracking-[0.08em] sm:text-7xl">Plastica belongs to whoever can survive it.</h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-white/75">
                            Build a single persistent character, pledge yourself to one of Plastica's nations, step into city locations,
                            roleplay in live text chat, work for Plastic Credits, buy licences and gear, and climb military, economic,
                            and political ladders inside a browser-based world.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route('lobby') }}" class="inline-flex items-center justify-center rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Enter Game</a>
                            @else
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Account</a>
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-white/15 bg-white/5 px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em]">Returning Player</a>
                            @endauth
                        </div>
                        <div class="mt-10 grid gap-4 md:grid-cols-3">
                            <div class="rounded-3xl border border-white/10 bg-black/20 p-5">
                                <p class="font-['Teko'] text-3xl uppercase tracking-[0.16em] text-[#7ead59]">8</p>
                                <p class="mt-1 text-sm uppercase tracking-[0.22em] text-white/55">Seeded factions</p>
                            </div>
                            <div class="rounded-3xl border border-white/10 bg-black/20 p-5">
                                <p class="font-['Teko'] text-3xl uppercase tracking-[0.16em] text-[#7ead59]">7</p>
                                <p class="mt-1 text-sm uppercase tracking-[0.22em] text-white/55">Locations per capital</p>
                            </div>
                            <div class="rounded-3xl border border-white/10 bg-black/20 p-5">
                                <p class="font-['Teko'] text-3xl uppercase tracking-[0.16em] text-[#7ead59]">5</p>
                                <p class="mt-1 text-sm uppercase tracking-[0.22em] text-white/55">Core progression loops</p>
                            </div>
                        </div>
                    </section>

                    <aside class="grid gap-6">
                        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30 backdrop-blur">
                            <p class="font-['Teko'] text-2xl uppercase tracking-[0.18em] text-[#c2a84f]">Core Game Flow</p>
                            <ol class="mt-4 grid gap-3 text-sm text-white/75">
                                <li class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">Register and log in with Laravel Breeze auth.</li>
                                <li class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">Choose a faction and create one persistent character.</li>
                                <li class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">Enter faction cities, move through locations, and roleplay in chat.</li>
                                <li class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">Work, trade, buy licences and items, and invest in companies.</li>
                                <li class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">Push your rank, wealth, military score, and influence onto the leaderboards.</li>
                            </ol>
                        </section>

                        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30 backdrop-blur">
                            <p class="font-['Teko'] text-2xl uppercase tracking-[0.18em] text-[#c2a84f]">Default Factions</p>
                            <div class="mt-4 grid gap-3">
                                @foreach ($factions as $faction)
                                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                                        <p class="font-['Teko'] text-2xl uppercase tracking-[0.1em]">{{ $faction->name }}</p>
                                        <p class="text-sm text-white/65">{{ $faction->short_description }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    </aside>
                </main>
            </div>
        </div>
    </body>
</html>
