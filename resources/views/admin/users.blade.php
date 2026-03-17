<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Users</p></x-slot>

    @include('admin.partials.nav')

    <div x-data="{ openId: null }" class="space-y-6">
        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Name</th>
                            <th class="px-5 py-4 text-left">Email</th>
                            <th class="px-5 py-4 text-left">Character</th>
                            <th class="px-5 py-4 text-left">Admin</th>
                            <th class="px-5 py-4 text-left">Icons</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($users as $user)
                            <tr class="align-top">
                                <td class="px-5 py-4 font-semibold text-white">{{ $user->name }}</td>
                                <td class="px-5 py-4">{{ $user->email }}</td>
                                <td class="px-5 py-4">{{ $user->character?->name ? $user->character->name.' | '.($user->character->faction?->name ?? 'No faction') : 'None' }}</td>
                                <td class="px-5 py-4">{{ $user->is_admin ? 'Yes' : 'No' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($user->accountIcons as $accountIcon)
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-black/20" title="{{ $accountIcon->tooltip ?: $accountIcon->name }}" style="color: {{ $accountIcon->color ?: '#f4ecd0' }};">
                                                <i class="{{ $accountIcon->icon_value }}"></i>
                                            </span>
                                        @empty
                                            <span class="text-white/40">None</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="openId = openId === {{ $user->id }} ? null : {{ $user->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openId === {{ $user->id }}" x-cloak>
                                <td colspan="6" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 md:grid-cols-2 xl:grid-cols-3">
                                        @csrf
                                        @method('PATCH')
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $user->name }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="email" type="email" value="{{ $user->email }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="password" type="password" placeholder="New password">
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="checkbox" name="is_admin" value="1" @checked($user->is_admin)>
                                            Admin
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70 xl:col-span-3">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Account Icons</span>
                                            <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-4">
                                                @foreach ($accountIcons as $accountIcon)
                                                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3">
                                                        <input type="checkbox" name="account_icon_ids[]" value="{{ $accountIcon->id }}" @checked($user->accountIcons->contains('id', $accountIcon->id)) @disabled($accountIcon->slug === 'admin-crown' && $user->is_admin)>
                                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-black/20" style="color: {{ $accountIcon->color ?: '#f4ecd0' }};">
                                                            <i class="{{ $accountIcon->icon_value }}"></i>
                                                        </span>
                                                        <span>{{ $accountIcon->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </label>
                                        <div class="xl:col-span-3 flex justify-end">
                                            <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
