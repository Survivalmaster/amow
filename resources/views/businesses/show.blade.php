<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">{{ $business->name }}</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">{{ $business->faction?->name }} | {{ $business->type_label }}</p>
        </div>
    </x-slot>

    @include('store._marketplace-tabs', ['marketplaceSection' => 'businesses'])

    <div class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <div class="flex items-start gap-4">
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl border border-[#7ead59]/30 bg-[#7ead59]/10 text-3xl text-[#d7edc7]">
                        <i class="{{ $business->icon_class }}"></i>
                    </span>
                    <div>
                        <p class="font-['Teko'] text-4xl uppercase tracking-[0.08em]">{{ $business->name }}</p>
                        <p class="text-sm text-white/60">Owned by {{ $business->owner?->name }}.</p>
                    </div>
                </div>
                <p class="mt-5 text-sm leading-7 text-white/70">{{ $business->description ?: 'No description has been set yet.' }}</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Bank</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase text-[#c2a84f]">{{ number_format($business->bank_credits) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Members</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase">{{ number_format($business->members->where('status', 'active')->count()) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Your Status</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase">{{ $member?->status ? str($member->status)->title() : 'Visitor' }}</p>
                    </div>
                </div>
                @if ($member?->status === 'invited' && $character->faction_id === $business->faction_id)
                    <form method="POST" action="{{ route('businesses.join', $business) }}" class="mt-5">
                        @csrf
                        <button class="amow-action-button inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                            <i class="fa-solid fa-door-open"></i>
                            Join Business
                        </button>
                    </form>
                @endif
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Business Menu</p>
                <div class="mt-4 grid gap-3">
                    @forelse ($business->menuItems as $item)
                        <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-white">{{ $item->title }}</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.18em] text-white/45">{{ $businessTypes[$item->mode] ?? str($item->mode)->title() }}</p>
                                </div>
                                <p class="font-['Teko'] text-2xl text-[#c2a84f]">{{ $item->price !== null ? number_format($item->price) : 'Quote' }}</p>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-white/65">{{ $item->description ?: 'No details added.' }}</p>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-white/10 bg-black/20 p-4 text-sm text-white/55">No menu entries yet.</p>
                    @endforelse
                </div>
            </div>
        </section>

        @if ($isOwner)
            <section class="grid gap-6 xl:grid-cols-3">
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Add Menu Item</p>
                    <form method="POST" action="{{ route('businesses.menu.store', $business) }}" class="mt-4 grid gap-3">
                        @csrf
                        <input name="title" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" placeholder="Offer title" required>
                        <select name="mode" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" required>
                            @foreach ($businessTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <input name="price" type="number" min="0" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" placeholder="Price or blank for quote">
                        <textarea name="description" class="min-h-24 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" placeholder="What is included?"></textarea>
                        <button class="amow-action-button rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">Add Item</button>
                    </form>
                </div>

                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Roles & Invites</p>
                    <form method="POST" action="{{ route('businesses.roles.store', $business) }}" class="mt-4 grid gap-3">
                        @csrf
                        <input name="name" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" placeholder="Role name" required>
                        <input name="hourly_wage" type="number" min="0" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" placeholder="Hourly wage" required>
                        <button class="amow-action-button rounded-full bg-[#c2a84f] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">Create Role</button>
                    </form>
                    <form method="POST" action="{{ route('businesses.invites.store', $business) }}" class="mt-5 grid gap-3 border-t border-white/10 pt-5">
                        @csrf
                        <input name="character_name" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" placeholder="Character name" required>
                        <select name="player_business_role_id" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3">
                            <option value="">No role yet</option>
                            @foreach ($business->roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }} - {{ number_format($role->hourly_wage) }}/hr</option>
                            @endforeach
                        </select>
                        <button class="amow-action-button rounded-full border border-[#7ead59]/35 bg-[#7ead59]/10 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">Invite</button>
                    </form>
                </div>

                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Business Bank</p>
                    <div class="mt-4 rounded-2xl border border-white/10 bg-black/20 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Available</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase text-[#c2a84f]">{{ number_format($business->bank_credits) }}</p>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <form method="POST" action="{{ route('businesses.deposit', $business) }}" class="grid gap-2">
                            @csrf
                            <input name="amount" type="number" min="1" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" placeholder="Amount" required>
                            <button class="rounded-full border border-[#7ead59]/35 bg-[#7ead59]/10 px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">Deposit</button>
                        </form>
                        <form method="POST" action="{{ route('businesses.withdraw', $business) }}" class="grid gap-2">
                            @csrf
                            <input name="amount" type="number" min="1" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" placeholder="Amount" required>
                            <button class="rounded-full border border-[#c2a84f]/35 bg-[#c2a84f]/10 px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#f4d77a]">Withdraw</button>
                        </form>
                    </div>
                </div>
            </section>
        @endif

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Members</p>
                <div class="mt-4 grid gap-2">
                    @foreach ($business->members as $businessMember)
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-sm">
                            <span class="font-semibold text-white">{{ $businessMember->character?->name }}</span>
                            <span class="text-white/55">{{ $businessMember->role?->name ?? 'No role' }} | {{ str($businessMember->status)->title() }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Business Audit</p>
                <div class="mt-4 max-h-96 space-y-2 overflow-y-auto pr-1">
                    @forelse ($business->logs->sortByDesc('created_at') as $log)
                        <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <p class="text-sm font-semibold text-white">{{ $log->description }}</p>
                                <p class="text-xs text-white/40">{{ $log->created_at->format('d M H:i') }}</p>
                            </div>
                            <p class="mt-1 text-xs uppercase tracking-[0.18em] text-white/40">{{ str($log->type)->replace('_', ' ')->title() }} by {{ $log->actor?->name ?? 'System' }}</p>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-white/10 bg-black/20 p-4 text-sm text-white/55">No audit entries yet.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
