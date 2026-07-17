<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Discord Management</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Synced Discord roles and the members currently assigned to them.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.discord-management.index') }}" class="rounded-full border border-[#7ead59]/40 bg-[#7ead59]/15 px-5 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">Roles</a>
            <a href="{{ route('admin.discord-management.roster') }}" class="rounded-full border border-white/10 bg-white/5 px-5 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">Roster</a>
            <a href="{{ route('admin.discord-management.commands') }}" class="rounded-full border border-white/10 bg-white/5 px-5 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">Commands</a>
        </div>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/45">Roles</p>
                <p class="mt-2 font-['Teko'] text-4xl uppercase tracking-[0.08em]">{{ number_format($roles->count()) }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/45">Assignments</p>
                <p class="mt-2 font-['Teko'] text-4xl uppercase tracking-[0.08em]">{{ number_format($memberAssignmentCount) }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/45">Last Sync</p>
                <p class="mt-3 text-sm font-semibold text-[#d7edc7]">{{ $lastSyncedAt ? $lastSyncedAt->diffForHumans() : 'Not synced yet' }}</p>
            </div>
        </section>

        @if (! $categoryOverridesEnabled)
            <div class="rounded-[1.5rem] border border-[#c2a84f]/25 bg-[#c2a84f]/10 px-5 py-4 text-sm text-[#f4d77a]">
                Run <span class="font-semibold">php artisan migrate</span> to enable manual Discord role categories.
            </div>
        @endif

        @if ($errors->has('category'))
            <div class="rounded-[1.5rem] border border-[#c65b3f]/35 bg-[#c65b3f]/10 px-5 py-4 text-sm text-[#f0b29f]">
                {{ $errors->first('category') }}
            </div>
        @endif

        @if ($errors->has('category_name'))
            <div class="rounded-[1.5rem] border border-[#c65b3f]/35 bg-[#c65b3f]/10 px-5 py-4 text-sm text-[#f0b29f]">
                {{ $errors->first('category_name') }}
            </div>
        @endif

        @if ($errors->has('name'))
            <div class="rounded-[1.5rem] border border-[#c65b3f]/35 bg-[#c65b3f]/10 px-5 py-4 text-sm text-[#f0b29f]">
                {{ $errors->first('name') }}
            </div>
        @endif

        @if (session('status'))
            <div class="rounded-[1.5rem] border border-[#7ead59]/30 bg-[#7ead59]/10 px-5 py-4 text-sm text-[#d7edc7]">
                {{ session('status') }}
            </div>
        @endif

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30" x-data="{ showCategoryAdmin: false, openCategoryId: null }">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Role Categories</p>
                    <p class="mt-1 text-sm text-white/55">The main role view below is grouped by these categories.</p>
                </div>
                <button
                    type="button"
                    @click="showCategoryAdmin = ! showCategoryAdmin"
                    class="inline-flex items-center justify-center gap-2 rounded-full border border-[#7ead59]/35 bg-[#7ead59]/10 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]"
                >
                    <i class="fa-solid" :class="showCategoryAdmin ? 'fa-xmark' : 'fa-folder-plus'"></i>
                    <span x-text="showCategoryAdmin ? 'Close Categories' : 'Manage Categories'"></span>
                </button>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($roleGroups as $group)
                    <div class="rounded-[1.25rem] border border-white/10 bg-black/15 p-4">
                        <p class="truncate text-sm font-semibold text-white">{{ $group['label'] }}</p>
                        <div class="mt-3 flex items-center justify-between gap-3 text-xs text-white/50">
                            <span>{{ number_format($group['role_count']) }} roles</span>
                            <span>{{ number_format($group['member_count']) }} assignments</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div x-show="showCategoryAdmin" x-cloak class="mt-5 border-t border-white/10 pt-5">
                @if ($categoryManagementEnabled)
                    <form method="POST" action="{{ route('admin.discord-management.categories.store') }}" class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_7rem_auto]">
                        @csrf
                        <label class="grid gap-2 text-sm text-white/70">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Name</span>
                            <input class="rounded-xl border border-white/10 bg-black/25 px-3 py-2" name="name" placeholder="Leadership Roles" required>
                        </label>
                        <label class="grid gap-2 text-sm text-white/70">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Description</span>
                            <input class="rounded-xl border border-white/10 bg-black/25 px-3 py-2" name="description" placeholder="Roles used by leadership and command teams.">
                        </label>
                        <label class="grid gap-2 text-sm text-white/70">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Order</span>
                            <input class="rounded-xl border border-white/10 bg-black/25 px-3 py-2" type="number" min="0" max="65535" name="sort_order" placeholder="70">
                        </label>
                        <div class="flex items-end">
                            <button class="rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">Create</button>
                        </div>
                    </form>

                    <div class="mt-5 divide-y divide-white/10 overflow-hidden rounded-[1.25rem] border border-white/10">
                        @foreach ($roleCategories as $categoryKey => $category)
                            @continue(! $category['category'])
                            <div class="bg-black/10">
                                <div class="grid gap-3 px-4 py-3 md:grid-cols-[minmax(0,1fr)_7rem_auto] md:items-center">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-white">{{ $category['label'] }}</p>
                                        <p class="mt-1 truncate text-xs text-white/45">{{ $category['description'] }}</p>
                                        <p class="mt-1 text-[11px] uppercase tracking-[0.16em] text-white/30">{{ $categoryKey }}</p>
                                    </div>
                                    <div class="text-sm text-white/45">Order {{ $category['category']->sort_order }}</div>
                                    <div class="flex gap-2 md:justify-end">
                                        <button type="button" @click="openCategoryId = openCategoryId === {{ $category['category']->id }} ? null : {{ $category['category']->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                        <form method="POST" action="{{ route('admin.discord-management.categories.destroy', $category['category']) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </div>

                                <form x-show="openCategoryId === {{ $category['category']->id }}" x-cloak method="POST" action="{{ route('admin.discord-management.categories.update', $category['category']) }}" class="grid gap-3 border-t border-white/10 bg-black/15 px-4 py-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_7rem_auto]">
                                    @csrf
                                    @method('PATCH')
                                    <label class="grid gap-2 text-sm text-white/70">
                                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Name</span>
                                        <input class="rounded-xl border border-white/10 bg-black/25 px-3 py-2" name="name" value="{{ $category['category']->name }}" required>
                                    </label>
                                    <label class="grid gap-2 text-sm text-white/70">
                                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Description</span>
                                        <input class="rounded-xl border border-white/10 bg-black/25 px-3 py-2" name="description" value="{{ $category['category']->description }}">
                                    </label>
                                    <label class="grid gap-2 text-sm text-white/70">
                                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Order</span>
                                        <input class="rounded-xl border border-white/10 bg-black/25 px-3 py-2" type="number" min="0" max="65535" name="sort_order" value="{{ $category['category']->sort_order }}">
                                    </label>
                                    <div class="flex items-end">
                                        <button class="rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">Save</button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-white/55">Run <span class="font-semibold text-[#f4d77a]">php artisan migrate</span> to enable category management.</p>
                @endif
            </div>
        </section>

        <section
            class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30"
            x-data="{
                openGroups: JSON.parse(localStorage.getItem('discordManagementOpenGroups') || '{}'),
                isOpen(key) {
                    return this.openGroups[key] ?? key === 'staff';
                },
                toggleGroup(key) {
                    this.openGroups[key] = ! this.isOpen(key);
                    localStorage.setItem('discordManagementOpenGroups', JSON.stringify(this.openGroups));
                }
            }"
        >
            <div class="border-b border-white/10 px-5 py-4">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Current Discord Roles</p>
                <p class="mt-1 text-sm text-white/55">The bot refreshes this snapshot from Discord.</p>
            </div>

            <div class="divide-y divide-white/10">
                @forelse ($roleGroups as $group)
                    <div>
                        <button
                            type="button"
                            @click="toggleGroup('{{ $group['key'] }}')"
                            class="flex w-full items-center gap-4 bg-black/10 px-5 py-4 text-left transition hover:bg-white/[0.03]"
                        >
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-[#7ead59]/25 bg-[#7ead59]/10 text-[#7ead59]">
                                <i class="fa-solid" :class="isOpen('{{ $group['key'] }}') ? 'fa-folder-open' : 'fa-folder'"></i>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold text-white">{{ $group['label'] }}</span>
                                <span class="mt-0.5 block text-xs text-white/45">{{ $group['description'] }}</span>
                            </span>
                            <span class="hidden text-right text-xs uppercase tracking-[0.16em] text-white/50 sm:block">
                                {{ number_format($group['role_count']) }} roles
                                <span class="block normal-case tracking-normal text-white/35">{{ number_format($group['member_count']) }} assignments</span>
                            </span>
                            <i class="fa-solid fa-chevron-down text-xs text-white/45 transition" :class="isOpen('{{ $group['key'] }}') ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="isOpen('{{ $group['key'] }}')" x-cloak class="border-t border-white/10 bg-black/10 px-4 py-4">
                            <div class="grid gap-4 xl:grid-cols-2">
                                @foreach ($group['roles'] as $role)
                                    <details class="group/role overflow-hidden rounded-[1.25rem] border border-white/10 bg-[#07100c]/70">
                                        <summary class="cursor-pointer list-none p-4 transition hover:bg-white/[0.03]">
                                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex min-w-0 items-center gap-3">
                                                        <span class="h-3.5 w-3.5 shrink-0 rounded-full border border-white/15" style="background-color: {{ $role->color ?: '#7ead59' }}"></span>
                                                        <span class="truncate text-sm font-semibold text-white">{{ $role->name }}</span>
                                                        @if ($role->is_managed)
                                                            <span class="rounded-full border border-[#c2a84f]/30 bg-[#c2a84f]/10 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-[#f4d77a]">Managed</span>
                                                        @endif
                                                    </div>
                                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-white/45">
                                                        <span>Role ID {{ $role->discord_id }}</span>
                                                        <span>Position {{ $role->position }}</span>
                                                        <span>{{ number_format($role->member_count) }} members</span>
                                                    </div>
                                                </div>

                                                <div class="flex shrink-0 items-center gap-3">
                                                    @if ($categoryOverridesEnabled)
                                                        <form method="POST" action="{{ route('admin.discord-management.roles.category.update', $role) }}" onclick="event.stopPropagation()" class="w-44">
                                                            @csrf
                                                            @method('PATCH')
                                                            <label class="sr-only" for="discord-role-category-{{ $role->id }}">Category for {{ $role->name }}</label>
                                                            <select
                                                                id="discord-role-category-{{ $role->id }}"
                                                                name="category"
                                                                onchange="this.form.submit()"
                                                                class="w-full rounded-xl border border-white/10 bg-black/25 px-3 py-2 text-xs font-semibold text-white/75"
                                                            >
                                                                <option value="">Auto category</option>
                                                                @foreach ($roleCategories as $categoryKey => $category)
                                                                    <option value="{{ $categoryKey }}" @selected($role->category === $categoryKey)>{{ $category['label'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </form>
                                                    @else
                                                        <div class="w-44 rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-xs font-semibold text-white/35">Auto category</div>
                                                    @endif
                                                    <i class="fa-solid fa-chevron-down text-xs text-white/45 transition group-open/role:rotate-180"></i>
                                                </div>
                                            </div>

                                            @if ($role->members->isNotEmpty())
                                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                                    @foreach ($role->members->take(8) as $member)
                                                        <span class="inline-flex max-w-[14rem] items-center gap-2 rounded-full border border-white/10 bg-black/25 px-2.5 py-1.5 text-xs text-white/75">
                                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#17271e] text-[10px] font-bold text-[#f4ecd0]">
                                                                @if ($member->avatar_url)
                                                                    <img src="{{ $member->avatar_url }}" alt="{{ $member->display_name ?? $member->username ?? 'Discord member' }} avatar" class="h-full w-full object-cover">
                                                                @else
                                                                    {{ Str::upper(Str::substr($member->display_name ?? $member->username ?? '?', 0, 1)) }}
                                                                @endif
                                                            </span>
                                                            <span class="truncate">{{ $member->display_name ?? $member->username ?? 'Unknown member' }}</span>
                                                        </span>
                                                    @endforeach
                                                    @if ($role->member_count > 8)
                                                        <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-white/45">+{{ number_format($role->member_count - 8) }} more</span>
                                                    @endif
                                                </div>
                                            @else
                                                <p class="mt-4 text-sm text-white/45">No members currently have this role.</p>
                                            @endif
                                        </summary>

                                        <div class="border-t border-white/10 bg-black/15 px-4 py-4">
                                            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                                @forelse ($role->members as $member)
                                                    <div class="flex min-w-0 items-center gap-3 rounded-xl border border-white/10 bg-black/20 px-3 py-2.5">
                                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#17271e] text-sm font-bold text-[#f4ecd0]">
                                                            @if ($member->avatar_url)
                                                                <img src="{{ $member->avatar_url }}" alt="{{ $member->display_name ?? $member->username ?? 'Discord member' }} avatar" class="h-full w-full object-cover">
                                                            @else
                                                                {{ Str::upper(Str::substr($member->display_name ?? $member->username ?? '?', 0, 1)) }}
                                                            @endif
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="truncate text-sm font-semibold text-white">{{ $member->display_name ?? $member->username ?? 'Unknown member' }}</p>
                                                            <p class="truncate text-xs text-white/45">{{ $member->username ?? $member->discord_user_id }}</p>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-white/50">No members currently have this role.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-white/55">
                        No Discord roles have been synced yet. Start or restart the Discord bot after setting the website sync URL and secret.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
