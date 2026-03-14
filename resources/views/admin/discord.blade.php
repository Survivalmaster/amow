<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Discord</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Manage WPNN webhook delivery and embed defaults.</p>
        </div>
    </x-slot>

    @include('admin.partials.nav')

    <form method="POST" action="{{ route('admin.discord.update') }}" class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
        @csrf
        @method('PATCH')

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">WPNN Webhook</p>
            <div class="mt-5 grid gap-4">
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Webhook URL</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="url" name="wpnn_webhook_url" value="{{ old('wpnn_webhook_url', $settings->wpnn_webhook_url) }}" placeholder="https://discord.com/api/webhooks/...">
                </label>

                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/80">
                    <input type="checkbox" name="wpnn_enabled" value="1" @checked(old('wpnn_enabled', $settings->wpnn_enabled))>
                    <span>Enable WPNN announcements from Discord.</span>
                </label>
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Embed Defaults</p>
            <div class="mt-5 grid gap-4">
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Embed Color</span>
                    <input class="h-12 rounded-2xl border border-white/10 bg-black/25 px-4 py-2" type="text" name="wpnn_embed_color" value="{{ old('wpnn_embed_color', $settings->wpnn_embed_color) }}" placeholder="#C65B3F">
                </label>

                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Footer Text</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="text" name="wpnn_footer_text" value="{{ old('wpnn_footer_text', $settings->wpnn_footer_text) }}" placeholder="WPNN desk">
                </label>
            </div>

            <div class="mt-6 rounded-2xl border border-white/10 bg-black/20 p-4 text-sm text-white/60">
                <p>Bot identity stays in `.env`.</p>
                <p class="mt-2">Webhook URL and WPNN presentation are managed here so Discord output can be updated without redeploying the bot.</p>
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
