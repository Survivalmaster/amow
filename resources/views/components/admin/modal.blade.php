@props([
    'open',
    'close' => null,
    'title',
    'subtitle' => null,
    'maxWidth' => '42rem',
])

@php($closeAction = $close ?? "{$open} = false")
@php($isAdminArea = request()->routeIs('admin.*'))
@php($overlayStyle = $isAdminArea
    ? 'position: fixed; inset: 0; z-index: 9999; background: rgba(3, 7, 18, 0.78); backdrop-filter: blur(4px);'
    : 'position: fixed; inset: 0; z-index: 9999; background: rgba(2, 4, 3, 0.94); backdrop-filter: blur(3px);')
@php($panelStyle = $isAdminArea
    ? "max-width: {$maxWidth}; max-height: 86vh; background: #111827; border-color: #263244; color: #e5e7eb; box-shadow: 0 24px 80px rgba(0, 0, 0, 0.45);"
    : "max-width: {$maxWidth}; max-height: 86vh; background: #07100c;")

<template x-teleport="body">
    <div
        x-show="{{ $open }}"
        x-cloak
        x-transition.opacity
        @keydown.escape.window="{{ $closeAction }}"
        class="flex items-center justify-center p-4"
        style="{{ $overlayStyle }}"
    >
        <div
            @click.outside="{{ $closeAction }}"
            class="w-full overflow-y-auto rounded-[1.25rem] border border-white/10 shadow-2xl shadow-black {{ $isAdminArea ? 'amow-admin-modal-panel' : '' }}"
            style="{{ $panelStyle }}"
        >
            <div class="flex items-start justify-between gap-4 border-b border-white/10 px-5 py-4">
                <div class="min-w-0">
                    <p class="truncate {{ $isAdminArea ? 'text-xl font-semibold normal-case tracking-normal text-slate-100' : 'font-[\'Teko\'] text-3xl uppercase leading-none tracking-[0.08em] text-white' }}">{{ $title }}</p>
                    @if ($subtitle)
                        <p class="mt-1 text-xs {{ $isAdminArea ? 'text-slate-400' : 'text-white/50' }}">{{ $subtitle }}</p>
                    @endif
                </div>
                <button type="button" @click="{{ $closeAction }}" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/60 transition hover:text-white" title="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{ $slot }}
        </div>
    </div>
</template>
