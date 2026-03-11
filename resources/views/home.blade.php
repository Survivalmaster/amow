<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Army Men of War') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=rajdhani:500,600,700|teko:500,600,700" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            :root {
                --bg: #08110d;
                --bg-deep: #020503;
                --panel: rgba(9, 22, 15, 0.82);
                --panel-strong: rgba(13, 34, 21, 0.92);
                --line: rgba(201, 224, 174, 0.16);
                --text: #f4f0db;
                --muted: #b2bc9f;
                --olive: #557d32;
                --olive-bright: #7eb451;
                --red: #b92a1a;
                --gold: #b69d2a;
                --blue: #1946bb;
                --shadow: 0 24px 80px rgba(0, 0, 0, 0.45);
            }

            * {
                box-sizing: border-box;
            }

            html {
                scroll-behavior: smooth;
            }

            body {
                margin: 0;
                min-height: 100vh;
                color: var(--text);
                font-family: "Rajdhani", sans-serif;
                background:
                    radial-gradient(circle at top, rgba(126, 180, 81, 0.14), transparent 30%),
                    radial-gradient(circle at 85% 20%, rgba(185, 42, 26, 0.2), transparent 22%),
                    linear-gradient(180deg, rgba(2, 5, 3, 0.4), rgba(2, 5, 3, 0.92)),
                    linear-gradient(135deg, #122419 0%, #07100b 45%, #020503 100%);
                overflow-x: hidden;
            }

            body::before,
            body::after {
                content: "";
                position: fixed;
                inset: 0;
                pointer-events: none;
            }

            body::before {
                background:
                    linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
                background-size: 48px 48px;
                mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.75), transparent 85%);
                opacity: 0.2;
            }

            body::after {
                background:
                    radial-gradient(circle at center, transparent 45%, rgba(0, 0, 0, 0.6) 100%);
            }

            a {
                color: inherit;
            }

            .shell {
                position: relative;
                z-index: 1;
                width: min(1240px, calc(100% - 2rem));
                margin: 0 auto;
                padding: 1.2rem 0 3rem;
            }

            .topbar,
            .hero,
            .intel-grid {
                border: 1px solid var(--line);
                background: linear-gradient(180deg, rgba(17, 37, 24, 0.82), rgba(8, 18, 12, 0.9));
                box-shadow: var(--shadow);
                backdrop-filter: blur(14px);
            }

            .topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 0.9rem 1.1rem;
                border-radius: 1.25rem;
            }

            .brand-wrap {
                display: flex;
                align-items: center;
                gap: 0.9rem;
            }

            .rank-mark {
                width: clamp(6.4rem, 13vw, 8.8rem);
                height: auto;
                flex: 0 0 auto;
                filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.35));
            }

            .brand-copy strong,
            .eyebrow,
            .section-label,
            .chip,
            .status,
            .action,
            h1,
            h2 {
                font-family: "Teko", sans-serif;
                letter-spacing: 0.05em;
                text-transform: uppercase;
            }

            .brand-copy strong {
                display: block;
                font-size: 1.55rem;
                line-height: 1;
            }

            .brand-copy span,
            .top-links,
            .subcopy,
            .small-copy,
            .card p,
            .footer {
                color: var(--muted);
            }

            .top-links {
                display: flex;
                align-items: center;
                gap: 0.85rem;
                flex-wrap: wrap;
                justify-content: flex-end;
                font-size: 1rem;
            }

            .hero {
                position: relative;
                display: grid;
                grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.7fr);
                gap: 1.5rem;
                margin-top: 1.1rem;
                padding: clamp(1.35rem, 3vw, 2.4rem);
                border-radius: 1.9rem;
                overflow: hidden;
            }

            .hero::before {
                content: "";
                position: absolute;
                inset: 0;
                background:
                    radial-gradient(circle at 20% 30%, rgba(126, 180, 81, 0.16), transparent 28%),
                    radial-gradient(circle at 78% 18%, rgba(185, 42, 26, 0.18), transparent 22%);
                pointer-events: none;
            }

            .hero-copy,
            .hero-panel {
                position: relative;
                z-index: 1;
            }

            .eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 0.55rem;
                margin: 0;
                color: #d4dfa8;
                font-size: 1rem;
            }

            .eyebrow::before {
                content: "";
                width: 2.4rem;
                height: 0.2rem;
                border-radius: 999px;
                background: linear-gradient(90deg, var(--olive-bright), transparent);
            }

            h1 {
                margin: 0.45rem 0 0;
                max-width: 10.5ch;
                font-size: clamp(4rem, 12vw, 7.6rem);
                line-height: 0.84;
            }

            .subcopy {
                max-width: 40rem;
                margin: 1rem 0 0;
                font-size: clamp(1.15rem, 2vw, 1.35rem);
                line-height: 1.45;
            }

            .hero-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.9rem;
                margin-top: 1.6rem;
            }

            .action {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 3.3rem;
                padding: 0.8rem 1.3rem;
                border-radius: 999px;
                border: 1px solid transparent;
                text-decoration: none;
                font-size: 1.2rem;
                transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
            }

            .action:hover {
                transform: translateY(-2px);
            }

            .action-primary {
                color: #08110d;
                background: linear-gradient(180deg, #92c85d, #65993d);
                box-shadow: 0 12px 24px rgba(76, 124, 38, 0.35);
            }

            .action-secondary {
                border-color: var(--line);
                background: rgba(255, 255, 255, 0.03);
            }

            .hero-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 0.7rem;
                margin-top: 1.2rem;
            }

            .chip {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                padding: 0.45rem 0.8rem;
                border: 1px solid rgba(201, 224, 174, 0.16);
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.04);
                font-size: 0.98rem;
            }

            .chip::before {
                content: "";
                width: 0.55rem;
                height: 0.55rem;
                border-radius: 50%;
                background: var(--olive-bright);
                box-shadow: 0 0 0 4px rgba(126, 180, 81, 0.14);
            }

            .hero-panel {
                display: grid;
                gap: 1rem;
                align-content: start;
            }

            .panel-card {
                padding: 1.15rem;
                border: 1px solid var(--line);
                border-radius: 1.35rem;
                background: linear-gradient(180deg, rgba(13, 34, 21, 0.92), rgba(8, 18, 12, 0.85));
            }

            .section-label {
                margin: 0 0 0.55rem;
                color: #dbe6b1;
                font-size: 1.02rem;
            }

            .status {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                margin-bottom: 0.8rem;
                color: #d7e4aa;
                font-size: 1rem;
            }

            .status::before {
                content: "";
                width: 0.65rem;
                height: 0.65rem;
                border-radius: 50%;
                background: #8ed65d;
                box-shadow: 0 0 16px rgba(142, 214, 93, 0.8);
            }

            .playlist,
            .intel-list {
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .playlist li,
            .intel-list li {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                padding: 0.8rem 0;
                border-bottom: 1px solid rgba(201, 224, 174, 0.1);
            }

            .playlist li:last-child,
            .intel-list li:last-child {
                border-bottom: 0;
                padding-bottom: 0;
            }

            .playlist strong,
            .intel-list strong,
            .stat strong {
                font-size: 1.08rem;
            }

            .small-copy {
                font-size: 1rem;
                line-height: 1.5;
            }

            .intel-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 1rem;
                margin-top: 1.1rem;
                padding: 1rem;
                border-radius: 1.7rem;
            }

            .card {
                min-height: 100%;
                padding: 1.2rem;
                border: 1px solid rgba(201, 224, 174, 0.1);
                border-radius: 1.3rem;
                background: rgba(255, 255, 255, 0.035);
            }

            h2 {
                margin: 0 0 0.45rem;
                font-size: 1.45rem;
            }

            .stats {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.9rem;
                margin-top: 1rem;
            }

            .stat {
                padding: 0.9rem;
                border-radius: 1rem;
                background: rgba(255, 255, 255, 0.04);
                border: 1px solid rgba(201, 224, 174, 0.08);
            }

            .stat strong {
                display: block;
                font-family: "Teko", sans-serif;
                font-size: 2rem;
                line-height: 1;
            }

            .footer {
                margin-top: 1rem;
                text-align: center;
                font-size: 0.95rem;
            }

            @keyframes rise {
                from {
                    opacity: 0;
                    transform: translateY(18px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .hero,
            .intel-grid,
            .topbar {
                animation: rise 0.65s ease both;
            }

            .intel-grid {
                animation-delay: 0.08s;
            }

            @media (max-width: 980px) {
                .hero,
                .intel-grid,
                .stats {
                    grid-template-columns: 1fr;
                }

                h1 {
                    max-width: none;
                }
            }

            @media (max-width: 720px) {
                .shell {
                    width: min(100% - 1rem, 100%);
                    padding-top: 0.6rem;
                }

                .topbar {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .top-links {
                    justify-content: flex-start;
                }

                .rank-mark {
                    width: 3.4rem;
                    height: auto;
                }

                h1 {
                    font-size: clamp(3.3rem, 18vw, 5rem);
                }

                .action {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class="shell">
            <header class="topbar">
                <div class="brand-wrap">
                    <svg class="rank-mark" viewBox="0 0 210 180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                        <path d="M105 8L145 95H65L105 8Z" fill="#b92a1a" stroke="#0a0f08" stroke-width="6" />
                        <path d="M105 38L134 102H76L105 38Z" fill="#b69d2a" stroke="#0a0f08" stroke-width="6" />
                        <path d="M105 67L123 108H87L105 67Z" fill="#1946bb" stroke="#0a0f08" stroke-width="6" />
                        <path d="M24 116L186 116L178 152C153 166 125 172 105 172C85 172 57 166 32 152L24 116Z" fill="#557d32" stroke="#0a0f08" stroke-width="6" />
                        <path d="M18 118C41 129 72 136 105 136C138 136 169 129 192 118" fill="none" stroke="#7eb451" stroke-width="8" stroke-linecap="round" />
                        <text x="105" y="148" text-anchor="middle" fill="#f4f0db" font-size="24" font-family="Teko, sans-serif" letter-spacing="2">AMOW</text>
                    </svg>
                    <div class="brand-copy">
                        <strong>Army Men of War</strong>
                        <span>Command lobby for the next wave of online battles.</span>
                    </div>
                </div>

                <div class="top-links">
                    @if (Route::has('login'))
                        @auth
                            <a class="action action-secondary" href="{{ url('/dashboard') }}">Enter Command</a>
                        @else
                            <a href="#briefing">Mission Brief</a>
                            <a class="action action-secondary" href="{{ route('login') }}">Log In</a>
                            @if (Route::has('register'))
                                <a class="action action-primary" href="{{ route('register') }}">Enlist Now</a>
                            @endif
                        @endauth
                    @else
                        <span>Public landing screen active</span>
                    @endif
                </div>
            </header>

            <main class="hero">
                <section class="hero-copy">
                    <p class="eyebrow">Pre-Deployment Access</p>
                    <h1>Plastic soldiers. Real conquest.</h1>
                    <p class="subcopy">
                        Drop players into a front-page lobby that feels like a live strategy game: faction colors,
                        mission energy, and a clear route straight into the fight.
                    </p>

                    <div class="hero-actions">
                        @if (Route::has('login'))
                            @auth
                                <a class="action action-primary" href="{{ url('/dashboard') }}">Launch Dashboard</a>
                            @else
                                <a class="action action-primary" href="{{ route('login') }}">Start Operation</a>
                                @if (Route::has('register'))
                                    <a class="action action-secondary" href="{{ route('register') }}">Create Recruit Profile</a>
                                @endif
                            @endauth
                        @else
                            <a class="action action-primary" href="{{ url('/') }}">Refresh Briefing</a>
                        @endif
                    </div>

                    <div class="hero-meta">
                        <span class="chip">Live PvP Identity</span>
                        <span class="chip">Frontline Rankings</span>
                        <span class="chip">Faction-Driven Progression</span>
                    </div>
                </section>

                <aside class="hero-panel">
                    <div class="panel-card">
                        <div class="status">War Room Online</div>
                        <p class="section-label">Frontline Feed</p>
                        <ul class="playlist">
                            <li>
                                <strong>Skirmish Queue</strong>
                                <span>Open</span>
                            </li>
                            <li>
                                <strong>Clan Comms</strong>
                                <span>Hot</span>
                            </li>
                            <li>
                                <strong>Territory Push</strong>
                                <span>Tonight</span>
                            </li>
                        </ul>
                    </div>

                    <div class="panel-card" id="briefing">
                        <p class="section-label">Mission Brief</p>
                        <p class="small-copy">
                            The homepage now reads like a multiplayer staging area instead of a generic placeholder.
                            It gives first-time visitors a stronger world, stronger CTAs, and a clear pre-login purpose.
                        </p>
                    </div>
                </aside>
            </main>

            <section class="intel-grid">
                <article class="card">
                    <p class="section-label">Why It Works</p>
                    <h2>Strong game-first framing</h2>
                    <p>
                        The first screen sells the fantasy immediately with command-center language, tactical panels,
                        and a sharper visual direction pulled from the Army Men of War emblem.
                    </p>
                </article>

                <article class="card">
                    <p class="section-label">Player Path</p>
                    <h2>Clean pre-login funnel</h2>
                    <ul class="intel-list">
                        <li>
                            <strong>Visitors</strong>
                            <span>See the world</span>
                        </li>
                        <li>
                            <strong>Returning users</strong>
                            <span>Log in fast</span>
                        </li>
                        <li>
                            <strong>New recruits</strong>
                            <span>Register from hero</span>
                        </li>
                    </ul>
                </article>

                <article class="card">
                    <p class="section-label">Battle Stats</p>
                    <h2>Arcade lobby energy</h2>
                    <div class="stats">
                        <div class="stat">
                            <strong>3</strong>
                            <span>Faction colors in play</span>
                        </div>
                        <div class="stat">
                            <strong>24/7</strong>
                            <span>War room mood</span>
                        </div>
                        <div class="stat">
                            <strong>1</strong>
                            <span>Clear entry path</span>
                        </div>
                    </div>
                </article>
            </section>

            <p class="footer">Public entry screen for {{ config('app.name', 'Army Men of War') }} on {{ now()->format('j M Y') }}.</p>
        </div>
    </body>
</html>
