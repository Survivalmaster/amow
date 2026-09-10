<style>
    [x-cloak] { display: none !important; }
    .amow-navigation-layout { --navigation-width: 320px; --navigation-rail: 70px; --navigation-background: #09150e; --navigation-offset: var(--navigation-width); --navigation-topbar: 70px; }
    .amow-admin-layout { --navigation-width: 280px; --navigation-background: #111827; }
    .amow-navigation { background: var(--navigation-background); z-index: 50; }
    .amow-navigation-panel { height: 100dvh; min-height: 0; padding: 0; }
    .amow-navigation-brand { height: var(--navigation-topbar); flex-shrink: 0; display: flex; align-items: center; justify-content: center; gap: 1rem; border-bottom: 1px solid rgb(255 255 255 / .1); font-weight: 700; font-size: 1.2rem; }
    .amow-navigation-brand-small { display: none; }
    .amow-navigation-brand .amow-navigation-close { margin: 0; align-self: auto; }
    .amow-navigation-character { margin: 1rem .75rem 0; }
    .amow-navigation-scroll { padding: 1rem .75rem; scrollbar-width: thin; scrollbar-color: #526357 transparent; overscroll-behavior: contain; }
    .amow-navigation-footer { flex-shrink: 0; margin: 0; padding: .75rem; border-top: 1px solid rgb(255 255 255 / .1); }
    .amow-navigation-toolbar { position: fixed; top: 0; right: 0; left: var(--navigation-offset); height: var(--navigation-topbar); display: flex; align-items: center; padding: 0 1.25rem; background: var(--navigation-background); border-bottom: 1px solid rgb(255 255 255 / .1); z-index: 45; }
    .amow-navigation-content { padding-top: var(--navigation-topbar); }
    .amow-navigation-overlay { position: fixed; inset: 0; z-index: 49; background: rgb(0 0 0 / .65); }
    .amow-nav-link { width: 100%; min-height: 42px; border-radius: .25rem; padding: .65rem .75rem; gap: .75rem; }
    .amow-nav-marker { display: none; }
    .amow-nav-link > i:first-of-type { width: 20px; flex-shrink: 0; text-align: center; }
    .amow-nav-label { text-align: left; }
    .amow-admin-nav-group { border: 0; background: none; padding: 0; }
    .amow-admin-group-toggle { justify-content: flex-start; font-size: .875rem; letter-spacing: normal; text-transform: none; }
    .amow-admin-group-toggle > span { flex: 1; }
    .amow-admin-group-toggle .amow-group-icon { font-size: 1rem; color: inherit; }
    .amow-nav-item > button { transition: background-color 180ms ease, color 180ms ease; }
    .amow-nav-item > button:active { background-color: rgb(255 255 255 / .09) !important; }
    .amow-nav-chevron, .amow-admin-group-toggle > i:last-child { transition: transform 240ms cubic-bezier(.22, 1, .36, 1); }
    .amow-nav-chevron.rotate-180 { transform: rotate(180deg); }
    .amow-nav-submenu { display: grid; grid-template-rows: 0fr; opacity: 0; padding-left: 1rem; margin-top: 0; transition: grid-template-rows 260ms cubic-bezier(.22, 1, .36, 1), opacity 180ms ease; }
    .amow-nav-submenu.is-open { grid-template-rows: 1fr; opacity: 1; }
    .amow-nav-submenu-content { min-height: 0; overflow: hidden; }
    @media (min-width: 768px) {
        .amow-navigation-layout { display: grid; grid-template-columns: var(--navigation-offset) minmax(0, 1fr); }
        .amow-navigation-layout.navigation-collapsed { --navigation-offset: var(--navigation-rail); }
        .amow-navigation-content { grid-column: 2; }
        .amow-navigation.amow-navigation { position: fixed; inset: 0 auto 0 0; height: 100dvh; width: var(--navigation-offset); border-right: 1px solid rgb(255 255 255 / .1); }
        .amow-navigation-close, .amow-navigation-overlay, .amow-navigation-mobile-brand { display: none !important; }
        .navigation-collapsed .amow-navigation-character, .navigation-collapsed .amow-navigation-brand-full { display: none; }
        .navigation-collapsed .amow-navigation-brand-small { display: inline; }
        .navigation-collapsed .amow-navigation-scroll { padding: .75rem 0; }
        .navigation-collapsed .amow-navigation-scroll > div > p { height: 24px; font-size: 0; text-align: center; }
        .navigation-collapsed .amow-navigation-scroll > div > p::after { content: '\2022\2022\2022'; font-size: 12px; letter-spacing: 2px; }
        .navigation-collapsed .amow-navigation-scroll .mt-7 { margin-top: .75rem; }
        .navigation-collapsed .amow-nav-item { position: relative; }
        .navigation-collapsed .amow-nav-link { justify-content: center; padding: .7rem 0; gap: 0; min-height: 44px; }
        .navigation-collapsed .amow-nav-link > i:first-of-type { font-size: 20px; }
        .navigation-collapsed .amow-nav-link > span, .navigation-collapsed .amow-nav-link > i:not(:first-of-type) { display: none; }
        .navigation-collapsed .amow-nav-submenu { display: none !important; }
        .navigation-collapsed .amow-nav-submenu { opacity: 1; transition: none; }
        .navigation-collapsed .amow-nav-item:is(:hover, :focus-within) > .amow-nav-submenu { display: block !important; position: fixed; left: var(--navigation-rail); top: calc(var(--flyout-top, 70px) + 44px); width: 250px; max-height: calc(100dvh - var(--flyout-top, 70px) - 52px); overflow-y: auto; margin: 0; padding: .5rem; background: var(--navigation-background); border-radius: 0 0 .35rem .35rem; box-shadow: 4px 8px 20px rgb(0 0 0 / .3); z-index: 52; }
        .navigation-collapsed .amow-nav-item:is(:hover, :focus-within) > .amow-nav-link > .amow-nav-label,
        .navigation-collapsed .amow-nav-link:is(:hover, :focus-visible) > .amow-nav-label { display: flex; align-items: center; position: fixed; top: var(--flyout-top, 70px); left: var(--navigation-rail); width: 250px; height: 44px; padding: 0 1rem; background: var(--navigation-background); color: inherit; z-index: 53; font-size: .875rem; box-shadow: 4px 0 15px rgb(0 0 0 / .15); }
        .navigation-collapsed .amow-nav-submenu .amow-nav-link { justify-content: flex-start; padding: .65rem .75rem; gap: .75rem; }
        .navigation-collapsed .amow-nav-submenu .amow-nav-link > span:not(.amow-nav-marker) { display: inline; position: static; width: auto; height: auto; padding: 0; box-shadow: none; background: none; }
        .navigation-collapsed .amow-nav-submenu .amow-nav-link > i:first-of-type { font-size: 14px; }
        .navigation-collapsed .amow-navigation-footer { padding: .5rem; }
        .navigation-collapsed .amow-navigation-footer .amow-nav-link { font-size: 10px; }
    }
    @media (max-width: 767px) {
        .amow-navigation-layout { --navigation-offset: 0px; }
        .amow-navigation { position: fixed; inset: 0 auto 0 0; width: min(var(--navigation-width), 90vw); transform: translateX(-100%); visibility: hidden; transition: transform 180ms ease, visibility 180ms; }
        .navigation-open .amow-navigation { transform: translateX(0); visibility: visible; }
    }
    @media (prefers-reduced-motion: reduce) {
        .amow-navigation, .amow-nav-submenu, .amow-nav-chevron,
        .amow-nav-item > button, .amow-admin-group-toggle > i:last-child { transition: none; }
    }
</style>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('amowNavigation', () => ({
            collapsed: false,
            mobileOpen: false,
            desktop: window.matchMedia('(min-width: 768px)').matches,
            init() {
                this.root = this.$el;
                this.collapsed = window.innerWidth <= 1024;
                this.compactViewport = window.innerWidth <= 1024;
                try {
                    const saved = sessionStorage.getItem('amow-navigation-collapsed');
                    if (saved !== null && !this.compactViewport) this.collapsed = saved === 'true';
                } catch (_) {}
                this.root.querySelectorAll('.amow-navigation-scroll a, .amow-navigation-scroll button, .amow-navigation-scroll .cursor-not-allowed, .amow-navigation-footer a, .amow-navigation-footer button').forEach(link => {
                    link.classList.add('amow-nav-link');
                    const spans = [...link.children].filter(child => child.tagName === 'SPAN');
                    const marker = spans.find(span => span.classList.contains('w-1'));
                    marker?.classList.add('amow-nav-marker');
                    spans.find(span => span !== marker)?.classList.add('amow-nav-label');
                    if (link.closest('.amow-nav-submenu')) return;
                    link.addEventListener('mouseenter', () => this.positionFlyout(link));
                    link.addEventListener('focus', () => this.positionFlyout(link));
                });
                this.root.querySelectorAll('.amow-navigation a, .amow-navigation button').forEach(link => {
                    const label = link.textContent.trim();
                    if (label && !link.hasAttribute('aria-label')) link.setAttribute('aria-label', label);
                });
                this.media = window.matchMedia('(min-width: 768px)');
                this.onResize = () => {
                    this.desktop = this.media.matches;
                    this.closeMobile();
                };
                this.media.addEventListener('change', this.onResize);
                this.onWindowResize = () => {
                    const compactViewport = window.innerWidth <= 1024;
                    if (compactViewport !== this.compactViewport) {
                        this.compactViewport = compactViewport;
                        this.collapsed = compactViewport;
                        if (!compactViewport) {
                            try {
                                this.collapsed = sessionStorage.getItem('amow-navigation-collapsed') === 'true';
                            } catch (_) {}
                        }
                    }
                    const focused = document.activeElement?.closest('.amow-nav-item');
                    if (focused) this.positionFlyout(focused);
                };
                window.addEventListener('resize', this.onWindowResize);
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
                if (this.mobileOpen) this.$nextTick(() => this.root.querySelector('#amow-navigation').focus());
            },
            positionFlyout(element) {
                if (!this.desktop || !this.collapsed) return;
                const item = element.closest('.amow-nav-item') ?? element;
                const anchor = item.querySelector('.amow-nav-link') ?? item;
                const submenu = item.querySelector('.amow-nav-submenu');
                const height = Math.min((submenu?.scrollHeight ?? 0) + 44, window.innerHeight - 16);
                const top = Math.max(8, Math.min(anchor.getBoundingClientRect().top, window.innerHeight - height - 8));
                item.style.setProperty('--flyout-top', `${top}px`);
            },
            closeNavigation() {
                if (this.mobileOpen) return this.closeMobile();
                if (this.collapsed && this.root.querySelector('.amow-navigation').contains(document.activeElement)) {
                    this.$refs.toggle.focus();
                }
            },
            closeMobile() {
                if (!this.mobileOpen) return;
                this.mobileOpen = false;
                this.$nextTick(() => this.$refs.toggle.focus());
            },
            trapNavigationFocus(event) {
                if (!this.mobileOpen || this.desktop || event.key !== 'Tab') return;
                const panel = this.root.querySelector('#amow-navigation');
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
                window.removeEventListener('resize', this.onWindowResize);
                if (this.mobileOpen) document.body.style.overflow = this.previousOverflow ?? '';
            },
        }));
    });
</script>
