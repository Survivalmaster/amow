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
                    <tr class="border-b border-white/10 last:border-b-0">
                        <td class="px-3 py-3 text-white/55">${escapeHtml(user.last_seen_label)}</td>
                        <td class="px-3 py-3">
                            <p class="truncate font-semibold text-[#f4ecd0]">${escapeHtml(user.character_name || user.account_name)}</p>
                            <p class="truncate text-[13px] text-white/45">${escapeHtml(user.account_name)}</p>
                        </td>
                        <td class="px-3 py-3 font-mono text-[13px] text-white/70">${escapeHtml(user.current_path || user.current_page_name)}</td>
                        <td class="px-3 py-3 text-[#d7edc7]">${escapeHtml(user.current_activity_text || '-')}</td>
                    </tr>
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

            <div class="mt-5 overflow-hidden rounded-[1.5rem] border border-white/10 bg-black/10">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-fixed" data-online-list>
                        <thead class="border-b border-white/10 bg-black/20 text-left text-[11px] uppercase tracking-[0.18em] text-white/40">
                            <tr>
                                <th class="w-[140px] px-3 py-3 font-medium">Last Seen</th>
                                <th class="w-[32%] px-3 py-3 font-medium">Character / User</th>
                                <th class="w-[28%] px-3 py-3 font-medium">Page</th>
                                <th class="px-3 py-3 font-medium">Activity</th>
                            </tr>
                        </thead>
                        <tbody>
                @forelse ($onlineUsers as $user)
                            <tr class="border-b border-white/10 last:border-b-0">
                                <td class="px-3 py-3 text-white/55">{{ optional($user->last_seen_at)->timezone(config('app.timezone'))->format('H:i:s') ?? 'Unknown' }}</td>
                                <td class="px-3 py-3">
                                    <p class="truncate font-semibold text-[#f4ecd0]">{{ $user->character?->name ?? $user->name }}</p>
                                    <p class="truncate text-[13px] text-white/45">{{ $user->name }}</p>
                                </td>
                                <td class="px-3 py-3 font-mono text-[13px] text-white/70">{{ $user->current_path ?: ($user->current_page_name ?: 'Unknown Page') }}</td>
                                <td class="px-3 py-3 text-[#d7edc7]">{{ $user->current_activity_text ?: '-' }}</td>
                            </tr>
                @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm uppercase tracking-[0.2em] text-white/45">
                                    Nobody is currently online.
                                </td>
                            </tr>
                @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
