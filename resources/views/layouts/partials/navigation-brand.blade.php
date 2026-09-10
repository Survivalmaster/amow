<div class="amow-navigation-brand">
    <a href="{{ route($navigationRoute) }}" aria-label="{{ $navigationTitle }}">
        <span class="amow-navigation-brand-full">{{ $navigationTitle }}</span>
        <span class="amow-navigation-brand-small" aria-hidden="true">A</span>
    </a>
    @include('layouts.partials.navigation-close')
</div>
