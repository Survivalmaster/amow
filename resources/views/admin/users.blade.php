<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Users</p></x-slot>

    @include('admin.partials.nav')

    <div class="space-y-4">
        @foreach ($users as $user)
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                @csrf
                @method('PATCH')
                <div class="grid gap-4 xl:grid-cols-[1fr_1fr_140px_180px]">
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $user->name }}" required>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="email" type="email" value="{{ $user->email }}" required>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="password" type="password" placeholder="New password">
                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                        <input type="checkbox" name="is_admin" value="1" @checked($user->is_admin)>
                        Admin
                    </label>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-sm text-white/60">
                        Character:
                        {{ $user->character?->name ?? 'None' }}
                        @if ($user->character?->faction)
                            • {{ $user->character->faction->name }}
                        @endif
                    </div>
                    <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Update User</button>
                </div>
            </form>
        @endforeach
    </div>
</x-app-layout>
