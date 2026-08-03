@props([
    'icon',
    'title',
    'type' => 'button',
    'tone' => 'neutral',
    'disabled' => false,
])

@php
    $toneClass = match ($tone) {
        'danger' => 'border-[#c65b3f]/35 bg-[#c65b3f]/10 text-[#f0b29f] hover:bg-[#c65b3f]/20',
        'success' => 'border-[#7ead59]/35 bg-[#7ead59]/10 text-[#d7edc7] hover:bg-[#7ead59]/20',
        default => 'border-white/10 bg-white/5 text-white/75 hover:border-[#7ead59]/35 hover:text-[#d7edc7]',
    };
@endphp

<button
    type="{{ $type }}"
    title="{{ $title }}"
    @disabled($disabled)
    {{ $attributes->merge(['class' => "inline-flex h-9 w-9 items-center justify-center rounded-full border transition disabled:cursor-not-allowed disabled:opacity-40 {$toneClass}"]) }}
>
    <i class="fa-solid {{ $icon }}"></i>
</button>
