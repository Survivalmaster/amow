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
    @php($isAdminArea = request()->routeIs('admin.*'))

    <body
        @auth
            @if (auth()->user()->character)
                data-character-state-url="{{ route('characters.state') }}"
            @endif
            data-presence-url="{{ route('presence.store') }}"
            data-current-path="{{ request()->path() }}"
            data-current-page-name="{{ \Illuminate\Support\Str::of(request()->route()?->getName() ?? request()->path())->replace(['.', '-', '_'], ' ')->title() }}"
        @endauth
        class="{{ $isAdminArea ? 'amow-admin-shell min-h-screen bg-[#0b1117] font-sans antialiased text-slate-100' : 'min-h-screen bg-[radial-gradient(circle_at_top,_rgba(126,173,89,0.14),_transparent_30%),linear-gradient(180deg,_#102017_0%,_#07100c_55%,_#040806_100%)] font-sans antialiased text-[#f4ecd0]' }}"
    >
        @php($authUser = auth()->user()?->fresh())
        @php($chatCharacter = $authUser?->character?->loadMissing(['user.permissions']))
        <div class="{{ $isAdminArea ? 'min-h-screen bg-[#0b1117]' : 'min-h-screen bg-[rgba(4,8,6,0.35)]' }}">
            <div class="{{ $isAdminArea ? 'lg:grid lg:grid-cols-[280px_minmax(0,1fr)]' : 'lg:grid lg:grid-cols-[320px_minmax(0,1fr)]' }}">
                @if ($isAdminArea)
                    @include('layouts.admin-navigation')
                @else
                    @include('layouts.navigation')
                @endif

                <div class="min-w-0">
                    @isset($header)
                        <header class="{{ $isAdminArea ? 'border-b border-slate-800 bg-[#0f1720] px-4 py-5 sm:px-6 lg:px-8' : 'px-4 pt-8 sm:px-6 lg:px-8' }}">
                            <div class="{{ $isAdminArea ? 'amow-admin-header mx-auto max-w-[120rem]' : 'rounded-[2rem] border border-white/10 bg-white/5 px-6 py-5 shadow-2xl shadow-black/30 backdrop-blur' }}">
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

                    <main class="{{ $isAdminArea ? 'mx-auto max-w-[120rem] px-4 py-6 sm:px-6 lg:px-8' : 'px-4 py-8 sm:px-6 lg:px-8' }}">
                        @if (session('status'))
                            <div class="{{ $isAdminArea ? 'mb-5 rounded-lg border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100' : 'mb-6 rounded-2xl border border-[#7ead59]/35 bg-[#7ead59]/10 px-4 py-3 text-sm' }}">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="{{ $isAdminArea ? 'mb-5 rounded-lg border border-red-500/25 bg-red-500/10 px-4 py-3 text-sm text-red-100' : 'mb-6 rounded-2xl border border-[#c65b3f]/35 bg-[#c65b3f]/10 px-4 py-3 text-sm' }}">
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
                @unless ($isAdminArea)
                    @include('layouts.global-chat', ['chatCharacter' => $chatCharacter])
                @endunless
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
        @if (! (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'))))
            <script>
                (() => {
                    const getValue = (state, key) => state?.[key];

                    window.globalChatBox = function globalChatBox({ stateUrl, sendUrl, csrfToken, currentCharacter, currentFaction }) {
                        return {
                            stateUrl,
                            sendUrl,
                            csrfToken,
                            currentCharacter,
                            currentFaction,
                            activeTab: 'world',
                            unread: { world: 0, nation: 0, direct: 0 },
                            worldMessages: [],
                            nationMessages: [],
                            directMessages: [],
                            onlineCharacters: [],
                            onlineCount: 1,
                            selectedDirectCharacterId: null,
                            draft: '',
                            sending: false,
                            hidden: false,
                            isFetching: false,
                            pollTimer: null,
                            init() {
                                this.restoreState();
                                this.fetchState(true);
                                this.pollTimer = window.setInterval(() => this.fetchState(false), 2500);
                            },
                            activeMessages() {
                                if (this.activeTab === 'nation') return this.nationMessages;
                                if (this.activeTab === 'direct') return this.directMessages;
                                return this.worldMessages;
                            },
                            selectedDirectCharacterName() {
                                return this.onlineCharacters.find((character) => character.id === this.selectedDirectCharacterId)?.name || 'No target selected';
                            },
                            activeTabLabel() {
                                if (this.activeTab === 'nation') return `${this.currentFaction} channel`;
                                if (this.activeTab === 'direct') return this.selectedDirectCharacterId ? `Direct to ${this.selectedDirectCharacterName()}` : 'Choose an online user';
                                return 'World channel';
                            },
                            restoreState() {
                                try {
                                    const saved = JSON.parse(window.localStorage.getItem('amow-global-chat-state') || 'null');
                                    if (saved) this.hidden = Boolean(saved.hidden);
                                } catch (error) {
                                }
                            },
                            persistState() {
                                window.localStorage.setItem('amow-global-chat-state', JSON.stringify({ hidden: this.hidden }));
                            },
                            hideChat() {
                                this.hidden = true;
                                this.persistState();
                            },
                            showChat() {
                                this.hidden = false;
                                this.clearUnread();
                                this.persistState();
                                this.$nextTick(() => this.scrollToBottom());
                            },
                            setActiveTab(tab) {
                                this.activeTab = tab;
                                this.unread[tab] = 0;
                                this.scrollToBottom();
                            },
                            clearUnread() {
                                this.unread = { world: 0, nation: 0, direct: 0 };
                            },
                            totalUnreadCount() {
                                return this.unread.world + this.unread.nation + this.unread.direct;
                            },
                            applyUnread(channel, previousLastId, nextLastId) {
                                if (!previousLastId || previousLastId === nextLastId) return;
                                if (this.hidden || this.activeTab !== channel) this.unread[channel] += 1;
                            },
                            async fetchState(scrollToBottom = false) {
                                if (this.isFetching) return;

                                this.isFetching = true;

                                try {
                                    const url = new URL(this.stateUrl, window.location.origin);

                                    if (this.selectedDirectCharacterId) {
                                        url.searchParams.set('direct_character_id', this.selectedDirectCharacterId);
                                    }

                                    const response = await fetch(url.toString(), {
                                        headers: {
                                            Accept: 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                        },
                                        credentials: 'same-origin',
                                    });

                                    if (!response.ok) return;

                                    const payload = await response.json();
                                    const previousWorldLastId = this.worldMessages.length ? this.worldMessages[this.worldMessages.length - 1].id : null;
                                    const previousNationLastId = this.nationMessages.length ? this.nationMessages[this.nationMessages.length - 1].id : null;
                                    const previousDirectLastId = this.directMessages.length ? this.directMessages[this.directMessages.length - 1].id : null;

                                    this.worldMessages = Array.isArray(payload.world_messages) ? payload.world_messages : [];
                                    this.nationMessages = Array.isArray(payload.nation_messages) ? payload.nation_messages : [];
                                    this.directMessages = Array.isArray(payload.direct_messages) ? payload.direct_messages : [];
                                    this.onlineCharacters = Array.isArray(payload.online_characters) ? payload.online_characters : [];
                                    this.onlineCount = Number(payload.online_count || 1);
                                    this.selectedDirectCharacterId = payload.selected_direct_character_id || this.selectedDirectCharacterId;

                                    const nextWorldLastId = this.worldMessages.length ? this.worldMessages[this.worldMessages.length - 1].id : null;
                                    const nextNationLastId = this.nationMessages.length ? this.nationMessages[this.nationMessages.length - 1].id : null;
                                    const nextDirectLastId = this.directMessages.length ? this.directMessages[this.directMessages.length - 1].id : null;

                                    this.applyUnread('world', previousWorldLastId, nextWorldLastId);
                                    this.applyUnread('nation', previousNationLastId, nextNationLastId);
                                    this.applyUnread('direct', previousDirectLastId, nextDirectLastId);

                                    if (!this.hidden) {
                                        this.unread[this.activeTab] = 0;
                                    }

                                    this.$nextTick(() => {
                                        const activeMessages = this.activeMessages();
                                        const previousLastId = this.activeTab === 'world'
                                            ? previousWorldLastId
                                            : (this.activeTab === 'nation' ? previousNationLastId : previousDirectLastId);
                                        const nextLastId = activeMessages.length ? activeMessages[activeMessages.length - 1].id : null;

                                        if (!this.hidden && (scrollToBottom || previousLastId !== nextLastId)) {
                                            this.scrollToBottom();
                                        }
                                    });
                                } finally {
                                    this.isFetching = false;
                                }
                            },
                            async sendMessage() {
                                const message = this.draft.trim();

                                if (!message || this.sending || (this.activeTab === 'direct' && !this.selectedDirectCharacterId)) {
                                    return;
                                }

                                this.sending = true;

                                try {
                                    const response = await fetch(this.sendUrl, {
                                        method: 'POST',
                                        headers: {
                                            Accept: 'application/json',
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': this.csrfToken,
                                            'X-Requested-With': 'XMLHttpRequest',
                                        },
                                        credentials: 'same-origin',
                                        body: JSON.stringify({
                                            channel: this.activeTab,
                                            message,
                                            target_character_id: this.activeTab === 'direct' ? this.selectedDirectCharacterId : null,
                                        }),
                                    });

                                    if (!response.ok) return;

                                    this.draft = '';
                                    await this.fetchState(true);
                                } finally {
                                    this.sending = false;
                                }
                            },
                            scrollToBottom() {
                                if (this.$refs.messages) {
                                    this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                                }
                            },
                        };
                    };

                    const updateCharacterStateDom = (state) => {
                        document.querySelectorAll('[data-character-field]').forEach((element) => {
                            const value = getValue(state, element.dataset.characterField);

                            if (value !== undefined && value !== null) {
                                element.textContent = value;
                            }
                        });

                        document.querySelectorAll('[data-character-width]').forEach((element) => {
                            const value = getValue(state, element.dataset.characterWidth);

                            if (value !== undefined && value !== null) {
                                element.style.width = `${value}%`;
                            }
                        });

                        document.querySelectorAll('[data-character-toggle-disabled]').forEach((element) => {
                            const key = element.dataset.characterToggleDisabled;
                            const value = Boolean(getValue(state, key));

                            element.disabled = value;
                        });
                    };

                    const startCharacterStatePolling = () => {
                        const stateUrl = document.body.dataset.characterStateUrl;

                        if (!stateUrl) return;

                        let isFetching = false;

                        const fetchState = async () => {
                            if (isFetching) return;
                            isFetching = true;

                            try {
                                const response = await fetch(stateUrl, {
                                    headers: {
                                        Accept: 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest',
                                    },
                                    credentials: 'same-origin',
                                });

                                if (!response.ok) return;

                                const state = await response.json();

                                updateCharacterStateDom(state);
                                window.dispatchEvent(new CustomEvent('character-state:updated', { detail: state }));
                            } finally {
                                isFetching = false;
                            }
                        };

                        fetchState();
                        window.setInterval(fetchState, 3000);
                    };

                    const startPresenceHeartbeat = () => {
                        const presenceUrl = document.body.dataset.presenceUrl;

                        if (!presenceUrl) return;

                        let isSending = false;

                        const resolveCurrentActivity = () => {
                            const activityElement = document.querySelector('[data-presence-activity]');
                            if (activityElement) return activityElement.dataset.presenceActivity ?? '';
                            return document.body.dataset.currentActivity ?? '';
                        };

                        const sendHeartbeat = async () => {
                            if (isSending) return;
                            isSending = true;

                            try {
                                await fetch(presenceUrl, {
                                    method: 'POST',
                                    headers: {
                                        Accept: 'application/json',
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
                                        'X-Requested-With': 'XMLHttpRequest',
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify({
                                        current_path: document.body.dataset.currentPath,
                                        current_page_name: document.body.dataset.currentPageName,
                                        current_activity_text: resolveCurrentActivity(),
                                    }),
                                });
                            } finally {
                                isSending = false;
                            }
                        };

                        sendHeartbeat();
                        document.addEventListener('visibilitychange', () => {
                            if (document.visibilityState === 'visible') sendHeartbeat();
                        });
                        window.addEventListener('focus', sendHeartbeat);
                        window.setInterval(sendHeartbeat, 5000);
                    };

                    const startConstructionTimers = () => {
                        const timers = document.querySelectorAll('[data-construction-timer]');
                        if (!timers.length) return;

                        const gradientForPercent = (percent) => {
                            if (percent >= 100) return 'linear-gradient(90deg,#7ead59 0%,#d7edc7 100%)';
                            if (percent >= 75) return 'linear-gradient(90deg,#8fbe63 0%,#d7edc7 100%)';
                            if (percent >= 40) return 'linear-gradient(90deg,#c2a84f 0%,#f4ecd0 100%)';
                            return 'linear-gradient(90deg,#c65b3f 0%,#f0b29f 100%)';
                        };

                        const formatDuration = (totalSeconds) => {
                            const safeSeconds = Math.max(0, totalSeconds);
                            const hours = Math.floor(safeSeconds / 3600);
                            const minutes = Math.floor((safeSeconds % 3600) / 60);
                            const seconds = safeSeconds % 60;
                            return [hours, minutes, seconds].map((value) => String(value).padStart(2, '0')).join(':');
                        };

                        const updateTimers = () => {
                            const now = Date.now();

                            timers.forEach((timer) => {
                                const buildStart = Date.parse(timer.getAttribute('data-build-start') || '');
                                const buildComplete = Date.parse(timer.getAttribute('data-build-complete') || '');

                                if (!buildStart || !buildComplete || buildComplete <= buildStart) return;

                                const durationMs = Math.max(1000, buildComplete - buildStart);
                                const elapsedMs = Math.max(0, Math.min(durationMs, now - buildStart));
                                const remainingSeconds = Math.max(0, Math.ceil((buildComplete - now) / 1000));
                                const progressPercent = Math.max(0, Math.min(100, Math.round((elapsedMs / durationMs) * 100)));
                                const isComplete = now >= buildComplete;

                                const fill = timer.querySelector('[data-build-progress-fill]');
                                const percentLabel = timer.querySelector('[data-build-progress-percent]');
                                const remainingLabel = timer.querySelector('[data-build-progress-remaining]');
                                const statusRoot = timer.closest('.rounded-2xl') || timer.parentElement;
                                const statusLabel = statusRoot ? statusRoot.querySelector('[data-build-status]') : null;

                                if (fill) {
                                    fill.style.width = `${progressPercent}%`;
                                    fill.style.backgroundColor = progressPercent >= 100 ? '#7ead59' : progressPercent >= 40 ? '#c2a84f' : '#c65b3f';
                                    fill.style.backgroundImage = gradientForPercent(progressPercent);
                                }

                                if (percentLabel) percentLabel.textContent = `${progressPercent}%`;
                                if (remainingLabel) remainingLabel.textContent = isComplete ? '00:00:00' : formatDuration(remainingSeconds);
                                if (statusLabel) {
                                    statusLabel.textContent = isComplete ? 'Ready' : 'Building';
                                    statusLabel.style.color = isComplete ? '#d7edc7' : '#f4ecd0';
                                }
                            });
                        };

                        updateTimers();
                        window.setInterval(updateTimers, 1000);
                    };

                    document.addEventListener('DOMContentLoaded', () => {
                        startPresenceHeartbeat();
                        startCharacterStatePolling();
                        startConstructionTimers();
                    });
                })();
            </script>
        @endif
        @stack('scripts')
    </body>
</html>
