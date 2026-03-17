@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dashboardRoot = document.querySelector('[data-admin-overview]');

            if (!dashboardRoot) {
                return;
            }

            const stateUrl = dashboardRoot.dataset.adminOverviewStateUrl;
            let isFetching = false;

            const renderState = (payload) => {
                const countElements = dashboardRoot.querySelectorAll('[data-online-count]');
                const listElement = dashboardRoot.querySelector('[data-online-list]');

                countElements.forEach((element) => {
                    element.textContent = payload.online_count ?? 0;
                });

                if (!listElement) {
                    return;
                }

                const onlineUsers = Array.isArray(payload.online_users) ? payload.online_users : [];

                if (onlineUsers.length === 0) {
                    listElement.innerHTML = `
                        <div class="rounded-3xl border border-dashed border-white/10 px-4 py-10 text-center text-sm uppercase tracking-[0.2em] text-white/45">
                            Nobody is currently online.
                        </div>
                    `;
                    return;
                }

                listElement.innerHTML = onlineUsers.map((user) => `
                    <article class="rounded-3xl border border-white/10 bg-black/20 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em] text-[#f4ecd0]">${user.character_name || user.account_name}</p>
                                <p class="text-xs uppercase tracking-[0.2em] text-white/45">${user.account_name}</p>
                            </div>
                            <p class="text-[10px] uppercase tracking-[0.18em] text-[#7ead59]">Seen ${user.last_seen_label}</p>
                        </div>
                        <div class="mt-3 rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <p class="text-[10px] uppercase tracking-[0.2em] text-white/38">Current Page</p>
                            <p class="mt-1 font-['Teko'] text-2xl uppercase tracking-[0.06em] text-white">${user.current_page_name}</p>
                            <p class="mt-1 text-xs text-white/45">${user.current_path}</p>
                        </div>
                    </article>
                `).join('');
            };

            const fetchState = async () => {
                if (isFetching) {
                    return;
                }

                isFetching = true;

                try {
                    const response = await fetch(stateUrl, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        return;
                    }

                    renderState(await response.json());
                } finally {
                    isFetching = false;
                }
            };

            fetchState();
            window.setInterval(fetchState, 5000);
        });
    </script>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin Command</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Manage accounts, characters, and world content.</p>
        </div>
    </x-slot>

    @include('admin.partials.nav')

    <div data-admin-overview data-admin-overview-state-url="{{ route('admin.overview.state') }}" class="space-y-6">
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $label => $value)
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                    <p class="text-xs uppercase tracking-[0.24em] text-white/45">{{ ucfirst(str_replace('_', ' ', $label)) }}</p>
                    <p class="mt-3 font-['Teko'] text-5xl uppercase tracking-[0.1em]" @if ($label === 'online_users') data-online-count @endif>{{ number_format($value) }}</p>
                </div>
            @endforeach
        </div>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="font-['Teko'] text-4xl uppercase tracking-[0.1em]">Users Online</p>
                    <p class="mt-2 text-sm text-white/60">Live view of currently active users and the page they are on.</p>
                </div>
                <div class="rounded-[1.5rem] border border-[#7ead59]/25 bg-[#7ead59]/10 px-4 py-3 text-right">
                    <p class="text-[10px] uppercase tracking-[0.18em] text-white/45">Currently Online</p>
                    <p class="font-['Teko'] text-4xl uppercase text-[#d7edc7]" data-online-count>{{ $onlineUsers->count() }}</p>
                </div>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-2" data-online-list>
                @forelse ($onlineUsers as $user)
                    <article class="rounded-3xl border border-white/10 bg-black/20 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em] text-[#f4ecd0]">{{ $user->character?->name ?? $user->name }}</p>
                                <p class="text-xs uppercase tracking-[0.2em] text-white/45">{{ $user->name }}</p>
                            </div>
                            <p class="text-[10px] uppercase tracking-[0.18em] text-[#7ead59]">Seen {{ optional($user->last_seen_at)->timezone(config('app.timezone'))->format('H:i:s') ?? 'Unknown' }}</p>
                        </div>
                        <div class="mt-3 rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <p class="text-[10px] uppercase tracking-[0.2em] text-white/38">Current Page</p>
                            <p class="mt-1 font-['Teko'] text-2xl uppercase tracking-[0.06em] text-white">{{ $user->current_page_name ?: 'Unknown Page' }}</p>
                            <p class="mt-1 text-xs text-white/45">{{ $user->current_path ?: '/' }}</p>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-white/10 px-4 py-10 text-center text-sm uppercase tracking-[0.2em] text-white/45 lg:col-span-2">
                        Nobody is currently online.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
