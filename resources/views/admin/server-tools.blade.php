<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Server Tools</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Run guarded maintenance actions without opening Plesk.</p>
        </div>
    </x-slot>

    @include('admin.partials.nav')

    @php($toolResult = session('tool_result'))

    <div class="space-y-6">
        @if ($toolResult)
            <section class="rounded-[1.5rem] border {{ $toolResult['failed'] ? 'border-[#c65b3f]/40 bg-[#c65b3f]/10' : 'border-[#7ead59]/35 bg-[#7ead59]/10' }} p-5 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">{{ $toolResult['title'] }}</p>
                <div class="mt-4 space-y-3">
                    @foreach ($toolResult['results'] as $result)
                        <div class="rounded-xl border border-white/10 bg-black/25 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="font-mono text-sm text-white">{{ $result['label'] }}</p>
                                <span class="rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] {{ $result['exit_code'] === 0 && ! $result['timed_out'] ? 'border-[#7ead59]/30 bg-[#7ead59]/10 text-[#d7edc7]' : 'border-[#c65b3f]/35 bg-[#c65b3f]/10 text-[#f0b29f]' }}">
                                    {{ $result['timed_out'] ? 'Timed out' : ($result['exit_code'] === 0 ? 'OK' : 'Exit '.$result['exit_code']) }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-white/45">{{ $result['duration_seconds'] }}s</p>
                            <pre class="mt-3 max-h-72 overflow-auto whitespace-pre-wrap rounded-xl border border-white/10 bg-black/35 p-3 text-xs leading-5 text-white/70">{{ $result['output'] }}</pre>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Runtime</p>
                    <p class="mt-1 text-sm text-white/55">Commands run from <span class="font-mono text-[#d7edc7]">{{ $projectPath }}</span>.</p>
                    <p class="mt-1 text-sm text-white/55">Git repo <span class="font-mono text-[#d7edc7]">{{ $gitRepoPath ?: 'using deploy path working tree' }}</span> on <span class="font-mono text-[#d7edc7]">{{ $gitBranch }}</span>.</p>
                    <p class="mt-1 text-sm text-white/55">Git SSH <span class="font-mono text-[#d7edc7]">{{ $gitSshCommand ?: 'server default' }}</span>.</p>
                </div>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($binaries as $name => $binary)
                    <div class="rounded-xl border border-white/10 bg-black/25 p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-white/40">{{ $name }}</p>
                        <p class="mt-1 break-all font-mono text-xs text-white/75">{{ $binary }}</p>
                    </div>
                @endforeach
            </div>
            <label class="mt-5 block space-y-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                <span>Search</span>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/35"></i>
                    <input class="w-full rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 pl-9 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35" placeholder="Artisan or GitHub tools">
                </div>
            </label>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Artisan</p>
                    <p class="mt-1 text-sm text-white/55">Run allowlisted Laravel maintenance commands.</p>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach ($artisanCommands as $command)
                        <form method="POST" action="{{ route('admin.server-tools.run') }}">
                            @csrf
                            <input type="hidden" name="section" value="artisan">
                            <input type="hidden" name="action" value="{{ $command }}">
                            <button class="w-full rounded-xl border border-white/10 bg-black/25 px-4 py-3 text-left transition hover:border-[#7ead59]/35 hover:bg-[#7ead59]/10">
                                <span class="block font-mono text-sm text-white">artisan {{ $command }}</span>
                                <span class="mt-1 block text-xs text-white/45">Run now</span>
                            </button>
                        </form>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">GitHub</p>
                    <p class="mt-1 text-sm text-white/55">Pull and deploy updates from the configured repository.</p>
                </div>

                <div class="mt-5 grid gap-3">
                    @foreach ($githubActions as $action)
                        <form method="POST" action="{{ route('admin.server-tools.run') }}">
                            @csrf
                            <input type="hidden" name="section" value="github">
                            <input type="hidden" name="action" value="{{ $action }}">
                            <button class="w-full rounded-xl border border-white/10 bg-black/25 px-4 py-3 text-left transition hover:border-[#7ead59]/35 hover:bg-[#7ead59]/10">
                                <span class="block font-mono text-sm text-white">github {{ $action }}</span>
                                <span class="mt-1 block text-xs text-white/45">
                                    @switch($action)
                                        @case('deploy')
                                            Pull, install Composer dependencies, migrate, and optimize.
                                            @break
                                        @case('npm-build')
                                            Install npm dependencies and build frontend assets.
                                            @break
                                        @case('pull')
                                            Pull latest code with fast-forward only.
                                            @break
                                        @default
                                            Show repository status.
                                    @endswitch
                                </span>
                            </button>
                        </form>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
