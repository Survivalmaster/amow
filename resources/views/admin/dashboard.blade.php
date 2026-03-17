@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dashboardRoot = document.querySelector('[data-admin-overview]');

            if (!dashboardRoot) {
                return;
            }

            const stateUrl = dashboardRoot.dataset.adminOverviewStateUrl;
            let isFetching = false;
            const escapeHtml = (value) => {
                const input = String(value ?? '');

                return input
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            };

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
                    <div class="grid gap-2 border-b border-white/10 px-1 py-3 text-sm last:border-b-0 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,0.9fr)_minmax(0,1.2fr)_auto] lg:items-center lg:gap-4">
                        <p class="truncate font-medium text-[#f4ecd0]">${escapeHtml(user.account_name)}</p>
                        <p class="truncate text-white/72">${escapeHtml(user.character_name || 'No Character')}</p>
                        <p class="truncate text-white/62">${escapeHtml(user.current_page_name)}</p>
                        <p class="truncate text-[#d7edc7]">${escapeHtml(user.current_activity_text || '-')}</p>
                        <p class="text-[11px] uppercase tracking-[0.18em] text-[#7ead59] lg:text-right">${escapeHtml(user.last_seen_label)}</p>
                    </div>
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

            <div class="mt-5 rounded-[1.5rem] border border-white/10 bg-black/15 px-4 py-2" data-online-list>
                <div class="hidden border-b border-white/10 px-1 py-3 text-[11px] uppercase tracking-[0.18em] text-white/40 lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,0.9fr)_minmax(0,1.2fr)_auto] lg:gap-4">
                    <p>Account Name</p>
                    <p>Character Name</p>
                    <p>Page On</p>
                    <p>Activity</p>
                    <p class="lg:text-right">Last Updated</p>
                </div>
                @forelse ($onlineUsers as $user)
                    <div class="grid gap-2 border-b border-white/10 px-1 py-3 text-sm last:border-b-0 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,0.9fr)_minmax(0,1.2fr)_auto] lg:items-center lg:gap-4">
                        <p class="truncate font-medium text-[#f4ecd0]">{{ $user->name }}</p>
                        <p class="truncate text-white/72">{{ $user->character?->name ?? 'No Character' }}</p>
                        <p class="truncate text-white/62">{{ $user->current_page_name ?: 'Unknown Page' }}</p>
                        <p class="truncate text-[#d7edc7]">{{ $user->current_activity_text ?: '-' }}</p>
                        <p class="text-[11px] uppercase tracking-[0.18em] text-[#7ead59] lg:text-right">{{ optional($user->last_seen_at)->timezone(config('app.timezone'))->format('H:i:s') ?? 'Unknown' }}</p>
                    </div>
                @empty
                    <div class="px-4 py-10 text-center text-sm uppercase tracking-[0.2em] text-white/45">
                        Nobody is currently online.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
