<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Changelogs</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Release notes for the website and Discord bot.</p>
        </div>
    </x-slot>

    @include('admin.partials.nav')

    <div x-data="{ showCreate: false, openId: null }" class="space-y-6">
        <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Release Queue</p>
                    <p class="mt-1 text-sm text-white/55">Draft changelogs stay private. Released changelogs appear on the player page and post once to Discord.</p>
                </div>
                <button type="button" @click="showCreate = true" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c] transition hover:bg-[#d7edc7]">
                    <i class="fa-solid fa-plus"></i>Create Changelog
                </button>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-white/45">Released</p>
                    <p class="font-['Teko'] text-3xl text-[#d7edc7]">{{ number_format($changelogs->where('status', 'released')->count()) }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-white/45">Drafts</p>
                    <p class="font-['Teko'] text-3xl text-[#f4d77a]">{{ number_format($changelogs->where('status', 'draft')->count()) }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-white/45">Discord Sent</p>
                    <p class="font-['Teko'] text-3xl text-[#f0b29f]">{{ number_format($changelogs->whereNotNull('discord_message_sent_at')->count()) }}</p>
                </div>
            </div>

            <label class="mt-5 block">
                <span class="sr-only">Search</span>
                <input data-admin-search class="w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white outline-none placeholder:text-white/35 focus:border-[#7ead59]/60" placeholder="Search changelogs">
            </label>
        </section>

        <x-admin.modal open="showCreate" title="Create Changelog" subtitle="Save the draft, then publish from the changelog list when it is ready." max-width="72rem">
            <form method="POST" action="{{ route('admin.changelogs.store') }}">
                @include('admin.partials.changelog-form', ['changelog' => null, 'method' => null, 'close' => 'showCreate = false'])
            </form>
        </x-admin.modal>

        <section class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Version</th>
                            <th class="px-5 py-4 text-left">Title</th>
                            <th class="px-5 py-4 text-left">Status</th>
                            <th class="px-5 py-4 text-left">Discord</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($changelogs as $changelog)
                            <tr data-admin-row data-search="{{ str($changelog->version.' '.$changelog->title.' '.$changelog->summary.' '.collect($changelog->groupedFeatures())->flatten()->implode(' '))->lower() }}" class="transition hover:bg-white/[0.035]">
                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-white">{{ $changelog->version }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-white">{{ $changelog->title }}</p>
                                    <p class="mt-1 max-w-2xl truncate text-xs text-white/50">{{ $changelog->summary ?: 'No summary set.' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="rounded-full border border-white/10 bg-black/25 px-2 py-1 text-[10px] uppercase tracking-[0.16em]">{{ $changelog->status }}</span>
                                    @if ($changelog->released_at)
                                        <span class="mt-1 block text-xs text-white/45">{{ $changelog->released_at->format('d M Y H:i') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    @if ($changelog->discord_channel_id)
                                        <p class="font-semibold text-white">Channel {{ $changelog->discord_channel_id }}</p>
                                    @else
                                        <p class="font-semibold text-[#f0b29f]">No channel set</p>
                                    @endif

                                    @if ($changelog->discord_message_sent_at)
                                        <p class="text-xs text-[#d7edc7]">Sent {{ $changelog->discord_message_sent_at->format('d M Y H:i') }}</p>
                                    @elseif ($changelog->isReleased())
                                        <p class="text-xs text-[#f4d77a]">Pending bot delivery</p>
                                    @else
                                        <p class="text-xs text-white/45">Draft not queued</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if (! $changelog->isReleased())
                                            <form method="POST" action="{{ route('admin.changelogs.publish', $changelog) }}">
                                                @csrf
                                                <x-admin.icon-button icon="fa-paper-plane" title="Publish" type="submit" />
                                            </form>
                                        @endif
                                        <x-admin.icon-button icon="fa-pen" title="Edit" x-on:click="openId = {{ $changelog->id }}" />
                                        <form method="POST" action="{{ route('admin.changelogs.destroy', $changelog) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.icon-button icon="fa-trash" title="Delete" tone="danger" type="submit" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <x-admin.modal open="openId === {{ $changelog->id }}" close="openId = null" title="Edit {{ $changelog->version }}" subtitle="{{ $changelog->title }}" max-width="72rem">
                                <form method="POST" action="{{ route('admin.changelogs.update', $changelog) }}">
                                    @include('admin.partials.changelog-form', ['changelog' => $changelog, 'method' => 'PATCH', 'close' => 'openId = null'])
                                </form>
                            </x-admin.modal>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-white/55">No changelogs have been created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
