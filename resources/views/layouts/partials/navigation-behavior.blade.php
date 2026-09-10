<style>
    [x-cloak] { display: none !important; }
    .amow-navigation-layout { --navigation-width: 320px; }
    .amow-admin-layout { --navigation-width: 280px; }
    .amow-navigation { background: #09150e; z-index: 40; }
    .amow-navigation-panel { height: 100dvh; min-height: 0; }
    .amow-navigation-panel > :last-child { flex-shrink: 0; }
    .amow-navigation-toolbar { padding: 1rem; border-bottom: 1px solid rgb(255 255 255 / .1); }
    .amow-navigation-overlay { position: fixed; inset: 0; z-index: 39; background: rgb(0 0 0 / .65); }
    .amow-navigation-close { flex-shrink: 0; }
    @media (min-width: 1024px) {
        .amow-navigation-layout { display: grid; grid-template-columns: var(--navigation-width) minmax(0, 1fr); }
        .amow-navigation-layout.navigation-collapsed { grid-template-columns: 80px minmax(0, 1fr); }
        .amow-navigation { position: sticky; top: 0; height: 100dvh; width: var(--navigation-width); border-right-width: 1px; }
        .amow-navigation-close, .amow-navigation-overlay { display: none !important; }
        .navigation-collapsed .amow-navigation:not(:hover):not(:focus-within) { width: 80px; }
        .navigation-collapsed .amow-navigation:not(:hover):not(:focus-within) .amow-navigation-panel { padding: .75rem .5rem; }
        .navigation-collapsed .amow-navigation:not(:hover):not(:focus-within) .amow-navigation-panel > :not(.flex-1) { display: none; }
        .navigation-collapsed .amow-navigation:not(:hover):not(:focus-within) .flex-1 { padding: 0; }
        .navigation-collapsed .amow-navigation:not(:hover):not(:focus-within) .flex-1 span,
        .navigation-collapsed .amow-navigation:not(:hover):not(:focus-within) .flex-1 p,
        .navigation-collapsed .amow-navigation:not(:hover):not(:focus-within) .fa-chevron-down,
        .navigation-collapsed .amow-navigation:not(:hover):not(:focus-within) .fa-chevron-up { display: none; }
        .navigation-collapsed .amow-navigation:not(:hover):not(:focus-within) [x-show] { display: none !important; }
        .navigation-collapsed .amow-navigation:not(:hover):not(:focus-within) .amow-admin-nav-group > [x-show] { display: block !important; }
        .navigation-collapsed .amow-navigation:not(:hover):not(:focus-within) .amow-admin-group-toggle { display: none; }
        .navigation-collapsed .amow-navigation:not(:hover):not(:focus-within) .amow-admin-nav-group { padding: 0; border: 0; background: none; }
        .navigation-collapsed .amow-navigation:not(:hover):not(:focus-within) a,
        .navigation-collapsed .amow-navigation:not(:hover):not(:focus-within) button { justify-content: center; }
    }
    @media (max-width: 1023px) {
        .amow-navigation { position: fixed; inset: 0 auto 0 0; width: min(var(--navigation-width), 90vw); transform: translateX(-100%); visibility: hidden; transition: transform 180ms ease, visibility 180ms; }
        .navigation-open .amow-navigation { transform: translateX(0); visibility: visible; }
    }
    @media (prefers-reduced-motion: reduce) {
        .amow-navigation { transition: none; }
    }
</style>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('amowNavigation', () => ({
            collapsed: false,
            mobileOpen: false,
            desktop: window.matchMedia('(min-width: 1024px)').matches,
            init() {
                try {
                    this.collapsed = sessionStorage.getItem('amow-navigation-collapsed') === 'true';
                } catch (_) {}
                this.$el.querySelectorAll('.amow-navigation a, .amow-navigation button').forEach(link => {
                    const label = link.textContent.trim();
                    if (label && !link.hasAttribute('aria-label')) link.setAttribute('aria-label', label);
                });
                this.media = window.matchMedia('(min-width: 1024px)');
                this.onResize = () => {
                    this.desktop = this.media.matches;
                    this.closeMobile();
                };
                this.media.addEventListener('change', this.onResize);
                this.$watch('mobileOpen', open => {
                    if (open) {
                        this.previousOverflow = document.body.style.overflow;
                        document.body.style.overflow = 'hidden';
                    } else {
                        document.body.style.overflow = this.previousOverflow ?? '';
                    }
                });
            },
            toggleNavigation() {
                if (this.desktop) {
                    this.collapsed = !this.collapsed;
                    try {
                        sessionStorage.setItem('amow-navigation-collapsed', String(this.collapsed));
                    } catch (_) {}
                    return;
                }
                this.mobileOpen = !this.mobileOpen;
                if (this.mobileOpen) this.$nextTick(() => this.$el.querySelector('#amow-navigation').focus());
            },
            closeMobile() {
                if (!this.mobileOpen) return;
                this.mobileOpen = false;
                this.$nextTick(() => this.$refs.toggle.focus());
            },
            trapNavigationFocus(event) {
                if (!this.mobileOpen || this.desktop || event.key !== 'Tab') return;
                const panel = this.$el.querySelector('#amow-navigation');
                const elements = [...panel.querySelectorAll('a[href], button:not([disabled]), [tabindex="0"]')]
                    .filter(element => element.getClientRects().length);
                const first = elements[0];
                const last = elements[elements.length - 1];
                if (!first) return;
                if (event.shiftKey && (document.activeElement === first || document.activeElement === panel)) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            },
            destroy() {
                this.media.removeEventListener('change', this.onResize);
                if (this.mobileOpen) document.body.style.overflow = this.previousOverflow ?? '';
            },
        }));
    });
</script>
