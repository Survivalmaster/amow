<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Discord</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Manage Discord webhook endpoints separately from the slash commands that use them.</p>
        </div>
    </x-slot>

    @include('admin.partials.nav')

    <div x-data="{ openWebhookId: null, openCommandId: null }" class="space-y-6">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Create Webhook</p>
                    <p class="mt-2 text-sm text-white/60">A webhook is the actual Discord delivery endpoint. It stores the channel target, webhook URL, and default embed color.</p>
                </div>
                <div class="w-40 rounded-xl border border-white/10 bg-[#313338] p-3 text-xs text-[#dbdee1]" x-data="{ color: '#C65B3F' }">
                    <p class="font-semibold text-white">Webhook Preview</p>
                    <div class="mt-3 rounded-lg border-l-4 bg-[#2b2d31] p-3" :style="`border-left-color: ${color}`">
                        <p>Embed color sample</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.discord.store') }}" class="mt-5 grid gap-4 lg:grid-cols-2" x-data="{ color: '#C65B3F' }">
                @csrf
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Webhook Name</span>
                    <span class="text-xs text-white/45">Internal label for staff, such as WPNN or Faction News.</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="WPNN" required>
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Discord Channel ID</span>
                    <span class="text-xs text-white/45">The target Discord channel ID this webhook posts into.</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="channel_id" placeholder="123456789012345678" required>
                </label>
                <label class="grid gap-2 text-sm text-white/70 lg:col-span-2">
                    <span class="uppercase tracking-[0.18em] text-white/45">Webhook URL</span>
                    <span class="text-xs text-white/45">Paste the full Discord webhook URL created in the channel integrations panel.</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="url" name="webhook_url" placeholder="https://discord.com/api/webhooks/..." required>
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Embed Color</span>
                    <span class="text-xs text-white/45">Default side color used for posts sent through this webhook.</span>
                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3">
                        <input class="h-11 w-14 cursor-pointer rounded-lg border border-white/10 bg-transparent p-0" type="color" name="embed_color" x-model="color" value="#C65B3F" required>
                        <input class="min-w-0 flex-1 bg-transparent text-sm uppercase tracking-[0.18em] text-white/70 outline-none" x-model="color" readonly>
                    </div>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/80 self-end">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span>Webhook is active and can receive posts.</span>
                </label>
                <div class="lg:col-span-2 flex justify-end">
                    <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Webhook</button>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="border-b border-white/10 px-5 py-4">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Current Webhooks</p>
                <p class="mt-1 text-sm text-white/55">Existing Discord delivery endpoints.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Name</th>
                            <th class="px-5 py-4 text-left">Channel ID</th>
                            <th class="px-5 py-4 text-left">Color</th>
                            <th class="px-5 py-4 text-left">Status</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($webhooks as $webhook)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-white">{{ $webhook->name }}</td>
                                <td class="px-5 py-4">{{ $webhook->channel_id }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-4 w-4 rounded-full border border-white/10" style="background-color: {{ $webhook->embed_color }}"></span>
                                        {{ $webhook->embed_color }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">{{ $webhook->is_active ? 'Active' : 'Disabled' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="openWebhookId = openWebhookId === {{ $webhook->id }} ? null : {{ $webhook->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                        <form method="POST" action="{{ route('admin.discord.destroy', $webhook) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openWebhookId === {{ $webhook->id }}" x-cloak>
                                <td colspan="5" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.discord.update', $webhook) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 lg:grid-cols-2" x-data='@json(["color" => $webhook->embed_color])'>
                                        @csrf
                                        @method('PATCH')
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Webhook Name</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $webhook->name }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Discord Channel ID</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="channel_id" value="{{ $webhook->channel_id }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70 lg:col-span-2">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Webhook URL</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="url" name="webhook_url" value="{{ $webhook->webhook_url }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Embed Color</span>
                                            <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3">
                                                <input class="h-11 w-14 cursor-pointer rounded-lg border border-white/10 bg-transparent p-0" type="color" name="embed_color" x-model="color" required>
                                                <input class="min-w-0 flex-1 bg-transparent text-sm uppercase tracking-[0.18em] text-white/70 outline-none" x-model="color" readonly>
                                            </div>
                                        </label>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/80 self-end">
                                            <input type="checkbox" name="is_active" value="1" @checked($webhook->is_active)>
                                            <span>Webhook is active and can receive posts.</span>
                                        </label>
                                        <div class="lg:col-span-2 flex justify-end">
                                            <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save Webhook</button>
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

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Create Discord Command</p>
            <p class="mt-2 text-sm text-white/60">A command points to one webhook and controls the slash command name, who can use it, and how it is described in Discord.</p>

            <form method="POST" action="{{ route('admin.discord.commands.store') }}" class="mt-5 grid gap-4 lg:grid-cols-2" x-data="{ accessMode: 'anyone' }">
                @csrf
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Command Label</span>
                    <span class="text-xs text-white/45">Internal label for staff so you know what this command is used for.</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="WPNN News Command" required>
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Linked Webhook</span>
                    <span class="text-xs text-white/45">Choose which webhook this command should post through.</span>
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="discord_webhook_id" required>
                        <option value="">Select a webhook</option>
                        @foreach ($webhooks as $webhook)
                            <option value="{{ $webhook->id }}">{{ $webhook->name }} | {{ $webhook->channel_id }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Slash Command Name</span>
                    <span class="text-xs text-white/45">The exact Discord command users will type, without the leading slash.</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="command_name" placeholder="amownews" required>
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Command Description</span>
                    <span class="text-xs text-white/45">Shown in Discord when users browse or autocomplete the command.</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="command_description" placeholder="Post a news announcement to the selected webhook">
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Access Rule</span>
                    <span class="text-xs text-white/45">Choose whether anyone can use it or only members with a specific Discord role.</span>
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="access_mode" x-model="accessMode" required>
                        <option value="anyone">Anyone can use this command</option>
                        <option value="role">Specific role only</option>
                    </select>
                </label>
                <label x-show="accessMode === 'role'" x-cloak class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Discord Role ID</span>
                    <span class="text-xs text-white/45">Required only for role-restricted commands. Members need this role to use the command.</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="role_id" placeholder="805824212060078142">
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/80 self-end">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span>Command is active and should be registered in Discord.</span>
                </label>
                <div class="lg:col-span-2 flex justify-end">
                    <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Command</button>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="border-b border-white/10 px-5 py-4">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Current Commands</p>
                <p class="mt-1 text-sm text-white/55">Slash commands that are linked to one of the saved webhooks.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Label</th>
                            <th class="px-5 py-4 text-left">Command</th>
                            <th class="px-5 py-4 text-left">Webhook</th>
                            <th class="px-5 py-4 text-left">Access</th>
                            <th class="px-5 py-4 text-left">Status</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($commands as $command)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-white">{{ $command->name }}</td>
                                <td class="px-5 py-4">/{{ $command->command_name }}</td>
                                <td class="px-5 py-4">{{ $command->webhook->name }}</td>
                                <td class="px-5 py-4">{{ $command->access_mode === 'role' ? 'Role: '.$command->role_id : 'Anyone' }}</td>
                                <td class="px-5 py-4">{{ $command->is_active ? 'Active' : 'Disabled' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="openCommandId = openCommandId === {{ $command->id }} ? null : {{ $command->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                        <form method="POST" action="{{ route('admin.discord.commands.destroy', $command) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openCommandId === {{ $command->id }}" x-cloak>
                                <td colspan="6" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.discord.commands.update', $command) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 lg:grid-cols-2" x-data='@json(["accessMode" => $command->access_mode])'>
                                        @csrf
                                        @method('PATCH')
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Command Label</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $command->name }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Linked Webhook</span>
                                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="discord_webhook_id" required>
                                                @foreach ($webhooks as $webhook)
                                                    <option value="{{ $webhook->id }}" @selected($command->discord_webhook_id === $webhook->id)>{{ $webhook->name }} | {{ $webhook->channel_id }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Slash Command Name</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="command_name" value="{{ $command->command_name }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Command Description</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="command_description" value="{{ $command->command_description }}">
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Access Rule</span>
                                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="access_mode" x-model="accessMode" required>
                                                <option value="anyone">Anyone can use this command</option>
                                                <option value="role">Specific role only</option>
                                            </select>
                                        </label>
                                        <label x-show="accessMode === 'role'" x-cloak class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Discord Role ID</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="role_id" value="{{ $command->role_id }}">
                                        </label>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/80 self-end">
                                            <input type="checkbox" name="is_active" value="1" @checked($command->is_active)>
                                            <span>Command is active and should be registered in Discord.</span>
                                        </label>
                                        <div class="lg:col-span-2 flex justify-end">
                                            <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save Command</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-sm text-white/55">No Discord commands have been created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
