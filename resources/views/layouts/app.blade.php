<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Army Men of War Roleplay App') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=rajdhani:500,600,700|teko:500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        @stack('styles')

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
            <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        @endif
    </head>
    <body
        @auth
            @if (auth()->user()->character)
                data-character-state-url="{{ route('characters.state') }}"
            @endif
            data-presence-url="{{ route('presence.store') }}"
            data-current-path="{{ request()->path() }}"
            data-current-page-name="{{ \Illuminate\Support\Str::of(request()->route()?->getName() ?? request()->path())->replace(['.', '-', '_'], ' ')->title() }}"
        @endauth
        class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(126,173,89,0.14),_transparent_30%),linear-gradient(180deg,_#102017_0%,_#07100c_55%,_#040806_100%)] font-sans antialiased text-[#f4ecd0]"
    >
        @php($authUser = auth()->user()?->fresh())
        @php($chatCharacter = $authUser?->character?->loadMissing(['user.permissions']))
        <div class="min-h-screen bg-[rgba(4,8,6,0.35)]">
            <div class="lg:grid lg:grid-cols-[320px_minmax(0,1fr)]">
                @include('layouts.navigation')

                <div class="min-w-0">
                    @isset($header)
                        <header class="px-4 pt-8 sm:px-6 lg:px-8">
                            <div class="rounded-[2rem] border border-white/10 bg-white/5 px-6 py-5 shadow-2xl shadow-black/30 backdrop-blur">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    @if (! request()->routeIs('admin.*') && ($activeGameEvents ?? collect())->isNotEmpty())
                        <div class="px-4 pt-4 sm:px-6 lg:px-8">
                            <div class="space-y-3">
                                @foreach ($activeGameEvents as $activeGameEvent)
                                    <div class="rounded-[1.6rem] border border-[#c2a84f]/35 bg-[linear-gradient(135deg,rgba(194,168,79,0.18),rgba(194,168,79,0.06))] px-5 py-4 shadow-xl shadow-black/20">
                                        <div class="flex items-start gap-3">
                                            <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[#c2a84f]/35 bg-black/20 text-[#f4d77a]">
                                                <i class="fa-solid fa-exclamation"></i>
                                            </span>
                                            <div>
                                                <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-[#f4ecd0]">{{ $activeGameEvent->title }}</p>
                                                <p class="text-sm leading-6 text-white/78">{{ $activeGameEvent->body }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <main class="px-4 py-8 sm:px-6 lg:px-8">
                        @if (session('status'))
                            <div class="mb-6 rounded-2xl border border-[#7ead59]/35 bg-[#7ead59]/10 px-4 py-3 text-sm">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-6 rounded-2xl border border-[#c65b3f]/35 bg-[#c65b3f]/10 px-4 py-3 text-sm">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
        @auth
            @if ($chatCharacter)
                @include('layouts.global-chat', ['chatCharacter' => $chatCharacter])
            @endif
        @endauth
        @auth
            @if ($authUser && ! $authUser->discord_user_id)
                <div
                    x-data="{ open: !sessionStorage.getItem('amow-discord-link-dismissed'), copied: false }"
                    x-show="open"
                    x-cloak
                    class="fixed inset-0 z-[90] flex items-end justify-center bg-black/55 p-4 sm:items-center"
                >
                    <div @click.outside="open = false; sessionStorage.setItem('amow-discord-link-dismissed', '1')" class="w-full max-w-xl overflow-hidden rounded-[2rem] border border-white/10 bg-[linear-gradient(180deg,rgba(16,29,21,0.98),rgba(7,12,9,0.98))] shadow-2xl shadow-black/50">
                        <div class="border-b border-white/10 px-6 py-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-['Teko'] text-4xl uppercase tracking-[0.08em] text-[#f4ecd0]">Link Your Discord</p>
                                    <p class="mt-1 text-sm leading-6 text-white/58">
                                        Finish linking your Discord so the AMOW bot can recognize your account, pull your Discord ID into your profile, and unlock bot-driven features.
                                    </p>
                                </div>
                                <button @click="open = false; sessionStorage.setItem('amow-discord-link-dismissed', '1')" class="rounded-full border border-white/10 px-3 py-2 text-sm text-white/58 transition hover:text-white">
                                    Close
                                </button>
                            </div>
                        </div>

                        <div class="space-y-5 px-6 py-6">
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-[1.4rem] border border-white/10 bg-black/20 p-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/42">1</p>
                                    <p class="mt-2 font-['Teko'] text-2xl uppercase tracking-[0.08em] text-white">Generate</p>
                                    <p class="mt-1 text-xs leading-5 text-white/58">Use the code below or refresh it if needed.</p>
                                </div>
                                <div class="rounded-[1.4rem] border border-white/10 bg-black/20 p-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/42">2</p>
                                    <p class="mt-2 font-['Teko'] text-2xl uppercase tracking-[0.08em] text-white">Message Bot</p>
                                    <p class="mt-1 text-xs leading-5 text-white/58">Use `/amowlink code:YOURCODE` in Discord.</p>
                                </div>
                                <div class="rounded-[1.4rem] border border-white/10 bg-black/20 p-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/42">3</p>
                                    <p class="mt-2 font-['Teko'] text-2xl uppercase tracking-[0.08em] text-white">Done</p>
                                    <p class="mt-1 text-xs leading-5 text-white/58">Your Discord ID and username are stored on your AMOW account.</p>
                                </div>
                            </div>

                            <div class="rounded-[1.5rem] border border-[#7ead59]/25 bg-[#7ead59]/10 p-5">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/45">Link Code</p>
                                <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="font-mono text-2xl font-semibold tracking-[0.28em] text-[#f4ecd0]">
                                            {{ $authUser->discord_link_token && $authUser->discord_link_token_expires_at?->isFuture() ? $authUser->discord_link_token : 'No active code' }}
                                        </p>
                                        <p class="mt-2 text-xs text-white/55">
                                            @if ($authUser->discord_link_token && $authUser->discord_link_token_expires_at?->isFuture())
                                                Expires {{ $authUser->discord_link_token_expires_at->timezone(config('app.timezone'))->format('j M Y H:i') }}.
                                            @else
                                                Generate a fresh code if you need one.
                                            @endif
                                        </p>
                                    </div>
                                    @if ($authUser->discord_link_token && $authUser->discord_link_token_expires_at?->isFuture())
                                        <button
                                            type="button"
                                            @click="navigator.clipboard.writeText('{{ $authUser->discord_link_token }}'); copied = true; setTimeout(() => copied = false, 1800)"
                                            class="rounded-full border border-white/10 bg-black/25 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white/80"
                                        >
                                            <span x-show="!copied">Copy Code</span>
                                            <span x-show="copied" x-cloak>Copied</span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <form method="POST" action="{{ route('profile.discord-link.store') }}">
                                    @csrf
                                    <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">
                                        {{ $authUser->discord_link_token && $authUser->discord_link_token_expires_at?->isFuture() ? 'Refresh Link Code' : 'Generate Link Code' }}
                                    </button>
                                </form>

                                <a href="{{ route('profile.edit') }}" class="text-sm font-semibold text-white/62 transition hover:text-[#f4ecd0]">
                                    Open account settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endauth
        @stack('scripts')
    </body>
</html>
