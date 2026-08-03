@php($adminNavUser = auth()->user()->loadMissing('permissions'))
@php($adminSections = collect(config('admin_sections', [])))

<nav class="mb-6 flex flex-wrap gap-3">
    @foreach ($adminSections as $section => $definition)
        @continue(in_array($section, ['game_master', 'moderator'], true))
        @continue(! $adminNavUser->canAccessAdminSection($section))
        @php($routeGroup = Str::beforeLast($definition['route'], '.'))
        @php($isActive = request()->routeIs($definition['route']) || ($routeGroup !== 'admin' && request()->routeIs($routeGroup.'.*')))
        <a href="{{ route($definition['route']) }}" class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ $isActive ? 'border-[#7ead59]/40 bg-[#7ead59]/15 text-[#7ead59]' : 'border-white/10 bg-white/5' }}">{{ $definition['label'] }}</a>
    @endforeach

    @if ($adminNavUser->canAccessAdminSection('characters'))
        <a href="{{ route('admin.character-logs.index') }}" class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ request()->routeIs('admin.character-log.*', 'admin.character-logs.*') ? 'border-[#7ead59]/40 bg-[#7ead59]/15 text-[#7ead59]' : 'border-white/10 bg-white/5' }}">Character Logs</a>
    @endif
</nav>
