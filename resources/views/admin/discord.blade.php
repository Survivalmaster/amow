<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Discord</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Manage reusable Discord webhooks for bot announcements.</p>
        </div>
    </x-slot>

    @include('admin.partials.nav')

    <div x-data="{ openId: null }" class="space-y-6">
        <form method="POST" action="{{ route('admin.discord.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30" x-data="{ name: 'Webhook', color: '#C65B3F', accessMode: 'anyone' }">
            @csrf
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Create Webhook Command</p>
                    <p class="mt-2 text-sm text-white/60">Creates a Discord slash command tied to one webhook, one target channel, and one access rule.</p>
                </div>
                <div class="w-40 rounded-xl border border-white/10 bg-[#313338] p-3 text-xs text-[#dbdee1]">
                    <p class="font-semibold text-white" x-text="name || 'Webhook Name'"></p>
                    <div class="mt-3 rounded-lg border-l-4 bg-[#2b2d31] p-3" :style="`border-left-color: ${color || '#C65B3F'}`">
                        <p>Preview</p>
                    </div>
                </div>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" x-model="name" placeholder="WPNN" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="command_name" placeholder="amownews" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="command_description" placeholder="Post a news announcement to this webhook">
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="channel_id" placeholder="123456789012345678" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" type="url" name="webhook_url" placeholder="https://discord.com/api/webhooks/..." required>
                <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3">
                    <input class="h-11 w-14 cursor-pointer rounded-lg border border-white/10 bg-transparent p-0" type="color" name="embed_color" x-model="color" value="#C65B3F" required>
                    <input class="min-w-0 flex-1 bg-transparent text-sm uppercase tracking-[0.18em] text-white/70 outline-none" x-model="color" readonly>
                </div>
                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="access_mode" x-model="accessMode" required>
                    <option value="anyone">Anyone can use this command</option>
                    <option value="role">Specific role only</option>
                </select>
                <input x-show="accessMode === 'role'" x-cloak class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="role_id" placeholder="Discord role ID for access control">
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/80">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span>Webhook is active.</span>
                </label>
            </div>

            <div class="mt-5 flex justify-end">
                <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Webhook</button>
            </div>
        </form>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Name</th>
                            <th class="px-5 py-4 text-left">Command</th>
                            <th class="px-5 py-4 text-left">Channel ID</th>
                            <th class="px-5 py-4 text-left">Access</th>
                            <th class="px-5 py-4 text-left">Color</th>
                            <th class="px-5 py-4 text-left">Status</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($webhooks as $webhook)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-white">{{ $webhook->name }}</td>
                                <td class="px-5 py-4">/{{ $webhook->command_name }}</td>
                                <td class="px-5 py-4">{{ $webhook->channel_id }}</td>
                                <td class="px-5 py-4">{{ $webhook->access_mode === 'role' ? 'Role: '.$webhook->role_id : 'Anyone' }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-4 w-4 rounded-full border border-white/10" style="background-color: {{ $webhook->embed_color }}"></span>
                                        {{ $webhook->embed_color }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">{{ $webhook->is_active ? 'Active' : 'Disabled' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="openId = openId === {{ $webhook->id }} ? null : {{ $webhook->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                        <form method="POST" action="{{ route('admin.discord.destroy', $webhook) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openId === {{ $webhook->id }}" x-cloak>
                                <td colspan="7" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.discord.update', $webhook) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 lg:grid-cols-2" x-data='@json(["name" => $webhook->name, "color" => $webhook->embed_color, "accessMode" => $webhook->access_mode])'>
                                        @csrf
                                        @method('PATCH')
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" x-model="name" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="command_name" value="{{ $webhook->command_name }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="command_description" value="{{ $webhook->command_description }}" placeholder="Post a news announcement to this webhook">
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="channel_id" value="{{ $webhook->channel_id }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" type="url" name="webhook_url" value="{{ $webhook->webhook_url }}" required>
                                        <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3">
                                            <input class="h-11 w-14 cursor-pointer rounded-lg border border-white/10 bg-transparent p-0" type="color" name="embed_color" x-model="color" required>
                                            <input class="min-w-0 flex-1 bg-transparent text-sm uppercase tracking-[0.18em] text-white/70 outline-none" x-model="color" readonly>
                                        </div>
                                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="access_mode" x-model="accessMode" required>
                                            <option value="anyone">Anyone can use this command</option>
                                            <option value="role">Specific role only</option>
                                        </select>
                                        <input x-show="accessMode === 'role'" x-cloak class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="role_id" value="{{ $webhook->role_id }}" placeholder="Discord role ID for access control">
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/80">
                                            <input type="checkbox" name="is_active" value="1" @checked($webhook->is_active)>
                                            <span>Webhook is active.</span>
                                        </label>
                                        <div class="lg:col-span-2 flex justify-end">
                                            <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-white/55">No Discord webhooks have been created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
