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
                --bg-0: #020503;
                --bg-1: #08110d;
                --bg-2: #0d1b14;
                --panel: rgba(10, 22, 16, 0.92);
                --panel-soft: rgba(14, 30, 22, 0.84);
                --line: rgba(181, 207, 146, 0.13);
                --line-strong: rgba(181, 207, 146, 0.24);
                --text: #f0eddc;
                --muted: #9aa58e;
                --green: #7dc35a;
                --green-deep: #4d7e30;
                --gold: #b79f37;
                --red: #9d2a20;
                --shadow: 0 24px 80px rgba(0, 0, 0, 0.42);
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                color: var(--text);
                font-family: "Rajdhani", sans-serif;
                background:
                    radial-gradient(circle at top, rgba(125, 195, 90, 0.12), transparent 26%),
                    radial-gradient(circle at 92% 0%, rgba(157, 42, 32, 0.16), transparent 22%),
                    linear-gradient(180deg, rgba(2, 5, 3, 0.2), rgba(2, 5, 3, 0.92)),
                    linear-gradient(180deg, #0c1712 0%, #07100c 38%, #020503 100%);
            }

            body::before {
                content: "";
                position: fixed;
                inset: 0;
                pointer-events: none;
                background:
                    linear-gradient(rgba(255, 255, 255, 0.025) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255, 255, 255, 0.025) 1px, transparent 1px);
                background-size: 52px 52px;
                mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.55), transparent 88%);
                opacity: 0.22;
            }

            a {
                color: inherit;
                text-decoration: none;
            }

            img {
                max-width: 100%;
                display: block;
            }

            .shell {
                position: relative;
                width: min(1280px, calc(100% - 2rem));
                margin: 0 auto;
                padding: 1rem 0 2.5rem;
            }

            .topbar,
            .hero,
            .content-grid,
            .feature-strip {
                border: 1px solid var(--line);
                background: linear-gradient(180deg, rgba(12, 25, 18, 0.96), rgba(8, 16, 12, 0.92));
                box-shadow: var(--shadow);
                backdrop-filter: blur(16px);
            }

            .topbar {
                display: grid;
                grid-template-columns: auto 1fr auto;
                gap: 1rem;
                align-items: center;
                padding: 0.75rem 1rem;
                border-radius: 1.2rem;
            }

            .brand {
                display: flex;
                align-items: center;
                gap: 0.9rem;
            }

            .brand-logo {
                width: clamp(4.5rem, 8vw, 6rem);
                flex: 0 0 auto;
                filter: drop-shadow(0 10px 18px rgba(0, 0, 0, 0.35));
            }

            .brand-copy strong,
            .nav-links a,
            .eyebrow,
            .hero h1,
            .button,
            .block-label,
            .metric-value,
            .feature-title,
            .story-title,
            .quote-title {
                font-family: "Teko", sans-serif;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .brand-copy strong {
                display: block;
                font-size: 1.75rem;
                line-height: 0.95;
            }

            .brand-copy span,
            .hero-copy p,
            .hero-copy li,
            .story-copy,
            .quote-copy,
            .nav-meta,
            .hero-stat span,
            .news-item time,
            .leaderboard small {
                color: var(--muted);
            }

            .nav-links {
                display: flex;
                justify-content: center;
                gap: 1.2rem;
                flex-wrap: wrap;
            }

            .nav-links a {
                font-size: 1.08rem;
                color: #d9e3c4;
            }

            .nav-meta {
                display: flex;
                align-items: center;
                gap: 0.7rem;
                justify-content: flex-end;
                flex-wrap: wrap;
                font-size: 0.98rem;
            }

            .status-dot {
                width: 0.65rem;
                height: 0.65rem;
                border-radius: 50%;
                background: var(--green);
                box-shadow: 0 0 14px rgba(125, 195, 90, 0.8);
            }

            .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 3rem;
                padding: 0.78rem 1.2rem;
                border-radius: 999px;
                border: 1px solid transparent;
                font-size: 1.15rem;
                transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
            }

            .button:hover {
                transform: translateY(-2px);
            }

            .button-primary {
                color: #08110d;
                background: linear-gradient(180deg, #97d36e, #5f943c);
                box-shadow: 0 10px 22px rgba(76, 124, 38, 0.38);
            }

            .button-secondary {
                border-color: var(--line-strong);
                background: rgba(255, 255, 255, 0.03);
                color: var(--text);
            }

            .hero {
                display: grid;
                grid-template-columns: minmax(0, 1.2fr) minmax(340px, 0.8fr);
                gap: 1.2rem;
                margin-top: 1rem;
                padding: 1.15rem;
                border-radius: 1.6rem;
                overflow: hidden;
            }

            .hero-stage {
                position: relative;
                min-height: 560px;
                border: 1px solid rgba(181, 207, 146, 0.08);
                border-radius: 1.2rem;
                overflow: hidden;
                background:
                    linear-gradient(90deg, rgba(2, 7, 4, 0.92) 0%, rgba(2, 12, 7, 0.82) 42%, rgba(2, 9, 5, 0.45) 100%),
                    radial-gradient(circle at 70% 25%, rgba(183, 159, 55, 0.12), transparent 18%),
                    linear-gradient(120deg, #0e1e16 0%, #07100c 45%, #050906 100%);
            }

            .hero-stage::before {
                content: "";
                position: absolute;
                inset: 0;
                background:
                    linear-gradient(180deg, rgba(255, 255, 255, 0.02), transparent 18%),
                    radial-gradient(circle at right center, rgba(125, 195, 90, 0.08), transparent 30%);
                pointer-events: none;
            }

            .hero-copy {
                position: relative;
                z-index: 1;
                width: min(620px, 100%);
                padding: clamp(1.4rem, 3vw, 2.1rem);
            }

            .eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 0.65rem;
                margin: 0;
                color: #d4e0aa;
                font-size: 1.05rem;
            }

            .eyebrow::before {
                content: "";
                width: 2.2rem;
                height: 0.18rem;
                border-radius: 999px;
                background: linear-gradient(90deg, var(--green), transparent);
            }

            .hero h1 {
                margin: 0.5rem 0 0;
                max-width: 7ch;
                font-size: clamp(4.6rem, 10vw, 7.8rem);
                line-height: 0.82;
            }

            .hero-copy p {
                max-width: 40rem;
                margin: 1rem 0 0;
                font-size: clamp(1.08rem, 1.8vw, 1.28rem);
                line-height: 1.45;
            }

            .hero-actions {
                display: flex;
                gap: 0.85rem;
                flex-wrap: wrap;
                margin-top: 1.5rem;
            }

            .hero-highlights {
                display: flex;
                gap: 1rem;
                flex-wrap: wrap;
                margin: 1.4rem 0 0;
                padding: 0;
                list-style: none;
            }

            .hero-highlights li {
                min-width: 180px;
                padding: 0.8rem 0.95rem;
                border-radius: 1rem;
                border: 1px solid rgba(181, 207, 146, 0.1);
                background: rgba(255, 255, 255, 0.035);
            }

            .hero-highlights strong {
                display: block;
                margin-bottom: 0.15rem;
                font-size: 1.15rem;
                color: #edf1dc;
            }

            .hero-panel {
                display: grid;
                gap: 1rem;
                align-content: start;
            }

            .panel-card,
            .content-card,
            .feature-card {
                border: 1px solid var(--line);
                border-radius: 1.2rem;
                background: linear-gradient(180deg, rgba(14, 28, 20, 0.95), rgba(8, 16, 12, 0.9));
            }

            .panel-card {
                padding: 1rem;
            }

            .block-label {
                margin: 0 0 0.65rem;
                color: #d4e0aa;
                font-size: 1.02rem;
            }

            .panel-header {
                display: flex;
                align-items: center;
                gap: 0.55rem;
                margin-bottom: 0.95rem;
                font-family: "Teko", sans-serif;
                font-size: 1.16rem;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .stack,
            .news-list,
            .leaderboard {
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .stack li,
            .news-item,
            .leaderboard li {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                padding: 0.78rem 0;
                border-bottom: 1px solid rgba(181, 207, 146, 0.08);
            }

            .stack li:last-child,
            .news-item:last-child,
            .leaderboard li:last-child {
                border-bottom: 0;
                padding-bottom: 0;
            }

            .stack strong,
            .leaderboard strong {
                font-size: 1.08rem;
            }

            .hero-metrics {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.8rem;
            }

            .hero-stat {
                padding: 0.95rem;
                border-radius: 1rem;
                background: rgba(255, 255, 255, 0.035);
                border: 1px solid rgba(181, 207, 146, 0.08);
            }

            .metric-value {
                display: block;
                font-size: 2.4rem;
                line-height: 0.9;
                color: #edf1dc;
            }

            .feature-strip {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 1rem;
                margin-top: 1rem;
                padding: 1rem;
                border-radius: 1.4rem;
            }

            .feature-card {
                padding: 1rem;
                min-height: 150px;
            }

            .feature-icon {
                width: 2.5rem;
                height: 2.5rem;
                display: grid;
                place-items: center;
                margin-bottom: 0.7rem;
                border-radius: 0.8rem;
                background: linear-gradient(180deg, rgba(151, 211, 110, 0.2), rgba(183, 159, 55, 0.12));
                color: #eaf0d2;
                font-family: "Teko", sans-serif;
                font-size: 1.35rem;
            }

            .feature-title {
                margin: 0;
                font-size: 1.3rem;
            }

            .feature-card p {
                margin: 0.45rem 0 0;
                color: var(--muted);
                line-height: 1.5;
            }

            .content-grid {
                display: grid;
                grid-template-columns: minmax(0, 1.1fr) minmax(280px, 0.9fr) minmax(280px, 0.85fr);
                gap: 1rem;
                margin-top: 1rem;
                padding: 1rem;
                border-radius: 1.4rem;
            }

            .content-card {
                padding: 1rem;
            }

            .story-card {
                position: relative;
                min-height: 230px;
                padding: 1.1rem;
                border-radius: 1rem;
                overflow: hidden;
                background:
                    linear-gradient(180deg, rgba(5, 10, 7, 0.2), rgba(5, 10, 7, 0.88)),
                    linear-gradient(130deg, rgba(151, 211, 110, 0.09), transparent 32%),
                    linear-gradient(120deg, #18251d 0%, #0c1510 100%);
                border: 1px solid rgba(181, 207, 146, 0.08);
            }

            .story-card + .story-card {
                margin-top: 0.8rem;
            }

            .story-title {
                margin: 0.35rem 0 0;
                font-size: 2rem;
                line-height: 0.95;
            }

            .story-copy {
                margin: 0.7rem 0 0;
                max-width: 26rem;
                line-height: 1.5;
            }

            .news-item {
                display: block;
            }

            .news-item strong {
                display: block;
                font-size: 1.08rem;
                color: #edf1dc;
            }

            .news-item time {
                display: block;
                margin-top: 0.35rem;
                font-size: 0.98rem;
            }

            .quote-card {
                padding: 1rem;
                border-radius: 1rem;
                border: 1px solid rgba(181, 207, 146, 0.08);
                background: rgba(255, 255, 255, 0.03);
            }

            .quote-card + .quote-card {
                margin-top: 0.8rem;
            }

            .quote-title {
                font-size: 1.2rem;
                color: #edf1dc;
            }

            .quote-copy {
                margin: 0.35rem 0 0;
                line-height: 1.5;
            }

            @keyframes rise {
                from {
                    opacity: 0;
                    transform: translateY(14px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .topbar,
            .hero,
            .feature-strip,
            .content-grid {
                animation: rise 0.55s ease both;
            }

            .feature-strip {
                animation-delay: 0.05s;
            }

            .content-grid {
                animation-delay: 0.1s;
            }

            @media (max-width: 1100px) {
                .hero,
                .content-grid,
                .feature-strip,
                .hero-metrics {
                    grid-template-columns: 1fr;
                }

                .hero h1 {
                    max-width: none;
                }
            }

            @media (max-width: 860px) {
                .topbar {
                    grid-template-columns: 1fr;
                    justify-items: start;
                }

                .nav-links,
                .nav-meta {
                    justify-content: flex-start;
                }
            }

            @media (max-width: 720px) {
                .shell {
                    width: min(100% - 1rem, 100%);
                    padding-top: 0.6rem;
                }

                .brand {
                    align-items: flex-start;
                }

                .brand-logo {
                    width: 4.2rem;
                }

                .hero-stage,
                .feature-card,
                .story-card {
                    min-height: auto;
                }

                .hero h1 {
                    font-size: clamp(3.5rem, 18vw, 5.7rem);
                }

                .hero-actions .button {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class="shell">
            <header class="topbar">
                <div class="brand">
                    <img class="brand-logo" src="{{ asset('images/amowog.png') }}" alt="Army Men of War logo">
                    <div class="brand-copy">
                        <strong>Army Men of War</strong>
                        <span>Persistent warfare, criminal alliances, and total map control.</span>
                    </div>
                </div>

                <nav class="nav-links" aria-label="Primary">
                    <a href="#about">About</a>
                    <a href="#features">Features</a>
                    <a href="#news">News</a>
                    <a href="#rankings">Rankings</a>
                </nav>

                <div class="nav-meta">
                    <span class="status-dot" aria-hidden="true"></span>
                    <span>War room live</span>
                    @if (Route::has('login'))
                        @auth
                            <a class="button button-secondary" href="{{ url('/dashboard') }}">Enter Command</a>
                        @else
                            <a class="button button-secondary" href="{{ route('login') }}">Log In</a>
                            @if (Route::has('register'))
                                <a class="button button-primary" href="{{ route('register') }}">Play Now</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </header>

            <main class="hero" id="about">
                <section class="hero-stage">
                    <div class="hero-copy">
                        <p class="eyebrow">MMO Warfare Reimagined</p>
                        <h1>Plastic Soldiers. Real Conquest.</h1>
                        <p>
                            Build your battalion, run black-market operations, join clan wars, and fight for total
                            control in a browser-based battlefield designed for long-term rivalry.
                        </p>

                        <div class="hero-actions">
                            @if (Route::has('login'))
                                @auth
                                    <a class="button button-primary" href="{{ url('/dashboard') }}">Launch Dashboard</a>
                                @else
                                    @if (Route::has('register'))
                                        <a class="button button-primary" href="{{ route('register') }}">Join The War</a>
                                    @else
                                        <a class="button button-primary" href="{{ route('login') }}">Join The War</a>
                                    @endif
                                    <a class="button button-secondary" href="{{ route('login') }}">Returning Player</a>
                                @endauth
                            @else
                                <a class="button button-primary" href="{{ url('/') }}">Reload Briefing</a>
                            @endif
                        </div>

                        <ul class="hero-highlights">
                            <li>
                                <strong>Faction Warfare</strong>
                                Hold ground, defend supply routes, and attack rival territories.
                            </li>
                            <li>
                                <strong>Economy & Loot</strong>
                                Trade weapons, fund operations, and strip resources from the battlefield.
                            </li>
                            <li>
                                <strong>Persistent Identity</strong>
                                Grow your soldier, reputation, and alliances over time.
                            </li>
                        </ul>
                    </div>
                </section>

                <aside class="hero-panel">
                    <div class="panel-card">
                        <div class="panel-header">
                            <span class="status-dot" aria-hidden="true"></span>
                            War Room Online
                        </div>

                        <div class="hero-metrics">
                            <div class="hero-stat">
                                <span class="metric-value">12.4k</span>
                                <span>Troops deployed this week</span>
                            </div>
                            <div class="hero-stat">
                                <span class="metric-value">318</span>
                                <span>Active clan operations</span>
                            </div>
                            <div class="hero-stat">
                                <span class="metric-value">49</span>
                                <span>Contested sectors</span>
                            </div>
                        </div>
                    </div>

                    <div class="panel-card">
                        <p class="block-label">Live Operations</p>
                        <ul class="stack">
                            <li>
                                <strong>Skirmish Queue</strong>
                                <span>Open now</span>
                            </li>
                            <li>
                                <strong>Clan War Ladder</strong>
                                <span>Updating</span>
                            </li>
                            <li>
                                <strong>Supply Drop Event</strong>
                                <span>2h remaining</span>
                            </li>
                            <li>
                                <strong>Black Market Trade</strong>
                                <span>Surging</span>
                            </li>
                        </ul>
                    </div>
                </aside>
            </main>

            <section class="feature-strip" id="features">
                <article class="feature-card">
                    <div class="feature-icon">01</div>
                    <h2 class="feature-title">Faction Combat</h2>
                    <p>Choose your side, reinforce your command chain, and build long-running rivalries across the map.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-icon">02</div>
                    <h2 class="feature-title">Crime & Raids</h2>
                    <p>Hit supply depots, sabotage opponents, and use underground operations to gain the edge.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-icon">03</div>
                    <h2 class="feature-title">Progression</h2>
                    <p>Train stats, unlock gear paths, and turn a recruit into a battlefield specialist.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-icon">04</div>
                    <h2 class="feature-title">Persistent World</h2>
                    <p>Every action feeds a larger war economy, ranking system, and territorial balance of power.</p>
                </article>
            </section>

            <section class="content-grid">
                <article class="content-card">
                    <p class="block-label">Featured Stories</p>

                    <div class="story-card">
                        <p class="block-label">Campaign Update</p>
                        <h2 class="story-title">Sector control is now the center of the war.</h2>
                        <p class="story-copy">
                            Alliances can now contest strongholds, drain enemy resources, and trigger high-value war
                            alerts that pull entire battalions into battle.
                        </p>
                    </div>

                    <div class="story-card">
                        <p class="block-label">New Player Path</p>
                        <h2 class="story-title">From recruit to commander without the dead time.</h2>
                        <p class="story-copy">
                            New players get clearer combat roles, faster early unlocks, and more reasons to join a clan
                            on day one.
                        </p>
                    </div>
                </article>

                <article class="content-card" id="news">
                    <p class="block-label">Latest Intel</p>
                    <ul class="news-list">
                        <li class="news-item">
                            <strong>Patch notes: convoy ambush rewards increased</strong>
                            <time>Today</time>
                        </li>
                        <li class="news-item">
                            <strong>Weekend operation opens with double salvage payouts</strong>
                            <time>Friday 19:00 UTC</time>
                        </li>
                        <li class="news-item">
                            <strong>Clan recruitment board now supports role tags</strong>
                            <time>This week</time>
                        </li>
                        <li class="news-item">
                            <strong>Frontline balancing pass targets bunker defense loops</strong>
                            <time>Recently deployed</time>
                        </li>
                    </ul>
                </article>

                <article class="content-card" id="rankings">
                    <p class="block-label">Top Commanders</p>
                    <ul class="leaderboard">
                        <li>
                            <div>
                                <strong>General Havoc</strong>
                                <small>Territory control leader</small>
                            </div>
                            <span>#1</span>
                        </li>
                        <li>
                            <div>
                                <strong>Bravo Reign</strong>
                                <small>Most successful raids</small>
                            </div>
                            <span>#2</span>
                        </li>
                        <li>
                            <div>
                                <strong>Green Legion</strong>
                                <small>Highest clan war streak</small>
                            </div>
                            <span>#3</span>
                        </li>
                    </ul>

                    <div class="quote-card">
                        <div class="quote-title">Built for long-term rivalry</div>
                        <p class="quote-copy">
                            Players are not just passing through a match queue. They are building status, enemies,
                            alliances, and territory over time.
                        </p>
                    </div>

                    <div class="quote-card">
                        <div class="quote-title">Designed like a live service front page</div>
                        <p class="quote-copy">
                            The layout now signals scale, activity, and social proof first, which is the part Torn's
                            homepage gets right.
                        </p>
                    </div>
                </article>
            </section>
        </div>
    </body>
</html>
