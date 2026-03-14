<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Discord</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Manage WPNN webhook delivery and embed defaults.</p>
        </div>
    </x-slot>

    @include('admin.partials.nav')

    <form
        method="POST"
        action="{{ route('admin.discord.update') }}"
        class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]"
        x-data='{
            messagePrefix: @json(old('wpnn_message_prefix', $settings->wpnn_message_prefix)),
            authorName: @json(old('wpnn_author_name', $settings->wpnn_author_name)),
            authorIconUrl: @json(old('wpnn_author_icon_url', $settings->wpnn_author_icon_url)),
            thumbnailUrl: @json(old('wpnn_thumbnail_url', $settings->wpnn_thumbnail_url)),
            embedColor: @json(old('wpnn_embed_color', $settings->wpnn_embed_color)),
            footerText: @json(old('wpnn_footer_text', $settings->wpnn_footer_text)),
            showTimestamp: @json((bool) old('wpnn_show_timestamp', $settings->wpnn_show_timestamp)),
            sampleHeadline: "Plastic Front Bulletin",
            sampleAnnouncement: "The Green command has secured another district after a dawn offensive. Citizens are advised to remain alert while logistics teams stabilize supply lanes.",
            sampleImageUrl: "https://images.unsplash.com/photo-1511884642898-4c92249e20b6?auto=format&fit=crop&w=1200&q=80",
        }'
    >
        @csrf
        @method('PATCH')

        <div class="space-y-6">
            <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">WPNN Webhook</p>
                <div class="mt-5 grid gap-4">
                    <label class="grid gap-2 text-sm text-white/70">
                        <span class="uppercase tracking-[0.18em] text-white/45">Webhook URL</span>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="url" name="wpnn_webhook_url" value="{{ old('wpnn_webhook_url', $settings->wpnn_webhook_url) }}" placeholder="https://discord.com/api/webhooks/...">
                    </label>

                    <label class="grid gap-2 text-sm text-white/70">
                        <span class="uppercase tracking-[0.18em] text-white/45">Message Prefix</span>
                        <textarea class="min-h-24 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="wpnn_message_prefix" x-model="messagePrefix" placeholder="@everyone&#10;Breaking from WPNN...">{{ old('wpnn_message_prefix', $settings->wpnn_message_prefix) }}</textarea>
                    </label>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="grid gap-2 text-sm text-white/70">
                            <span class="uppercase tracking-[0.18em] text-white/45">Author Name</span>
                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="text" name="wpnn_author_name" x-model="authorName" value="{{ old('wpnn_author_name', $settings->wpnn_author_name) }}" placeholder="World Plastica News Network">
                        </label>

                        <label class="grid gap-2 text-sm text-white/70">
                            <span class="uppercase tracking-[0.18em] text-white/45">Author Icon URL</span>
                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="url" name="wpnn_author_icon_url" x-model="authorIconUrl" value="{{ old('wpnn_author_icon_url', $settings->wpnn_author_icon_url) }}" placeholder="https://...">
                        </label>
                    </div>

                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/80">
                        <input type="checkbox" name="wpnn_enabled" value="1" @checked(old('wpnn_enabled', $settings->wpnn_enabled))>
                        <span>Enable WPNN announcements from Discord.</span>
                    </label>
                </div>
            </section>

            <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Embed Defaults</p>
                <div class="mt-5 grid gap-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="grid gap-2 text-sm text-white/70">
                            <span class="uppercase tracking-[0.18em] text-white/45">Embed Color</span>
                            <input class="h-12 rounded-2xl border border-white/10 bg-black/25 px-4 py-2" type="text" name="wpnn_embed_color" x-model="embedColor" value="{{ old('wpnn_embed_color', $settings->wpnn_embed_color) }}" placeholder="#C65B3F">
                        </label>

                        <label class="grid gap-2 text-sm text-white/70">
                            <span class="uppercase tracking-[0.18em] text-white/45">Thumbnail URL</span>
                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="url" name="wpnn_thumbnail_url" x-model="thumbnailUrl" value="{{ old('wpnn_thumbnail_url', $settings->wpnn_thumbnail_url) }}" placeholder="https://...">
                        </label>
                    </div>

                    <label class="grid gap-2 text-sm text-white/70">
                        <span class="uppercase tracking-[0.18em] text-white/45">Footer Text</span>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="text" name="wpnn_footer_text" x-model="footerText" value="{{ old('wpnn_footer_text', $settings->wpnn_footer_text) }}" placeholder="WPNN desk">
                    </label>

                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/80">
                        <input type="checkbox" name="wpnn_show_timestamp" value="1" x-model="showTimestamp" @checked(old('wpnn_show_timestamp', $settings->wpnn_show_timestamp))>
                        <span>Show timestamp on WPNN embeds.</span>
                    </label>
                </div>

                <div class="mt-6 rounded-2xl border border-white/10 bg-black/20 p-4 text-sm text-white/60">
                    <p>Bot credentials still stay in `.env`.</p>
                    <p class="mt-2">Webhook URL, presentation defaults, and formatting live here so staff can tune news output without changing the bot runtime.</p>
                </div>
            </section>
        </div>

        <section class="rounded-[2rem] border border-white/10 bg-[#111318] p-6 shadow-2xl shadow-black/30">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Preview</p>
                    <p class="text-sm uppercase tracking-[0.18em] text-white/40">Approximate Discord render</p>
                </div>
                <div class="rounded-full border border-white/10 bg-black/30 px-4 py-2 text-xs uppercase tracking-[0.22em] text-white/45">
                    Live
                </div>
            </div>

            <div class="mt-6 rounded-[1.5rem] border border-white/10 bg-[#313338] p-5 text-[#dbdee1]">
                <template x-if="messagePrefix">
                    <p class="mb-4 whitespace-pre-line text-sm text-[#f2f3f5]" x-text="messagePrefix"></p>
                </template>

                <div class="flex gap-4">
                    <div class="mt-1 h-10 w-10 shrink-0 rounded-full bg-[linear-gradient(135deg,#6fbb56,#214315)]"></div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <span class="font-semibold text-white">WPNN</span>
                            <span class="rounded bg-[#5865f2] px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-[0.16em] text-white">App</span>
                            <span class="text-xs text-[#949ba4]">Today at 18:00</span>
                        </div>

                        <div class="mt-3 overflow-hidden rounded-xl border-l-4 bg-[#2b2d31]" :style="`border-left-color: ${embedColor || '#c65b3f'}`">
                            <div class="p-4">
                                <div class="flex gap-4" :class="thumbnailUrl ? 'justify-between' : ''">
                                    <div class="min-w-0 flex-1">
                                        <template x-if="authorName">
                                            <div class="mb-2 flex items-center gap-2 text-xs text-white">
                                                <template x-if="authorIconUrl">
                                                    <img :src="authorIconUrl" alt="" class="h-5 w-5 rounded-full object-cover">
                                                </template>
                                                <span class="font-semibold" x-text="authorName"></span>
                                            </div>
                                        </template>

                                        <p class="text-base font-semibold text-white" x-text="sampleHeadline"></p>
                                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-[#dbdee1]" x-text="sampleAnnouncement"></p>
                                    </div>

                                    <template x-if="thumbnailUrl">
                                        <img :src="thumbnailUrl" alt="" class="h-20 w-20 rounded-lg object-cover">
                                    </template>
                                </div>

                                <img :src="sampleImageUrl" alt="" class="mt-4 max-h-72 w-full rounded-xl object-cover">

                                <div class="mt-4 flex items-center gap-2 text-xs text-[#949ba4]">
                                    <template x-if="footerText">
                                        <span x-text="`${footerText} | Posted by Admin User`"></span>
                                    </template>
                                    <template x-if="showTimestamp">
                                        <span>• Just now</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="lg:col-span-2 flex items-center justify-between gap-4">
            <div class="text-sm text-green-400">
                @if (session('status'))
                    {{ session('status') }}
                @endif
                @if ($errors->any())
                    {{ $errors->first() }}
                @endif
            </div>
            <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">
                Save Discord Settings
            </button>
        </div>
    </form>
</x-app-layout>
