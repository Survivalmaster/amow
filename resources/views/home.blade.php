<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|instrument-sans:400,500,600" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            :root {
                --bg: #f4efe4;
                --panel: rgba(255, 250, 240, 0.84);
                --ink: #1d1b18;
                --muted: #5b554d;
                --line: rgba(29, 27, 24, 0.12);
                --accent: #bc4f2c;
                --accent-strong: #7e2d16;
                --accent-soft: #f2c07b;
                --shadow: 0 24px 80px rgba(50, 33, 17, 0.14);
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                font-family: "Instrument Sans", sans-serif;
                color: var(--ink);
                background:
                    radial-gradient(circle at top left, rgba(242, 192, 123, 0.6), transparent 28%),
                    radial-gradient(circle at right center, rgba(188, 79, 44, 0.18), transparent 26%),
                    linear-gradient(135deg, #f7f2e7 0%, #efe3cf 45%, #eadbc2 100%);
            }

            .shell {
                width: min(1120px, calc(100% - 2rem));
                margin: 0 auto;
                padding: 2rem 0 3rem;
            }

            .nav,
            .hero,
            .metrics {
                border: 1px solid var(--line);
                background: var(--panel);
                backdrop-filter: blur(18px);
                box-shadow: var(--shadow);
            }

            .nav {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
                padding: 1rem 1.25rem;
                border-radius: 1.5rem;
            }

            .brand,
            .eyebrow,
            .label {
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .brand {
                font: 700 0.95rem/1 "Space Grotesk", sans-serif;
            }

            .nav-note,
            .copy,
            .metric p,
            .footer {
                color: var(--muted);
            }

            .hero {
                display: grid;
                grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
                gap: 2rem;
                margin-top: 1.25rem;
                padding: clamp(1.5rem, 4vw, 3rem);
                border-radius: 2rem;
            }

            .eyebrow {
                margin: 0 0 1rem;
                color: var(--accent-strong);
                font: 700 0.78rem/1 "Space Grotesk", sans-serif;
            }

            h1 {
                margin: 0;
                max-width: 10ch;
                font: 700 clamp(3rem, 8vw, 6.5rem) / 0.95 "Space Grotesk", sans-serif;
            }

            .copy {
                margin: 1.25rem 0 0;
                max-width: 42rem;
                font-size: clamp(1rem, 1.8vw, 1.15rem);
                line-height: 1.7;
            }

            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.85rem;
                margin-top: 2rem;
            }

            .button,
            .button-secondary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 3rem;
                padding: 0.85rem 1.25rem;
                border-radius: 999px;
                font-weight: 600;
                text-decoration: none;
            }

            .button {
                background: var(--accent);
                color: #fff7f0;
            }

            .button-secondary {
                border: 1px solid var(--line);
                color: var(--ink);
            }

            .panel {
                display: grid;
                gap: 1rem;
                align-content: start;
            }

            .card {
                border: 1px solid var(--line);
                border-radius: 1.5rem;
                padding: 1.25rem;
                background: rgba(255, 255, 255, 0.5);
            }

            .label {
                margin: 0 0 0.65rem;
                color: var(--accent-strong);
                font: 700 0.75rem/1 "Space Grotesk", sans-serif;
            }

            .stack {
                display: grid;
                gap: 0.75rem;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .stack li {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                padding-bottom: 0.75rem;
                border-bottom: 1px solid var(--line);
            }

            .stack li:last-child {
                padding-bottom: 0;
                border-bottom: 0;
            }

            .stack strong,
            .metric strong {
                font-family: "Space Grotesk", sans-serif;
            }

            .metrics {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 1rem;
                margin-top: 1.25rem;
                padding: 1rem;
                border-radius: 1.75rem;
            }

            .metric {
                padding: 1rem;
                border-radius: 1.25rem;
                background: rgba(255, 255, 255, 0.42);
            }

            .metric strong {
                display: block;
                font-size: 1.8rem;
            }

            .metric p {
                margin: 0.35rem 0 0;
                line-height: 1.5;
            }

            .footer {
                margin-top: 1rem;
                text-align: center;
                font-size: 0.92rem;
            }

            @media (max-width: 860px) {
                .hero,
                .metrics {
                    grid-template-columns: 1fr;
                }

                h1 {
                    max-width: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="shell">
            <header class="nav">
                <div>
                    <div class="brand">{{ config('app.name', 'Laravel') }}</div>
                    <div class="nav-note">Custom homepage wired into your Laravel routes and views.</div>
                </div>
                <div class="nav-note">Route: <strong>/</strong></div>
            </header>

            <main class="hero">
                <section>
                    <p class="eyebrow">Homepage Check</p>
                    <h1>Laravel is serving your site.</h1>
                    <p class="copy">
                        This page is coming from <code>resources/views/home.blade.php</code> through the
                        existing <code>routes/web.php</code> file, so you can use it as a quick proof that the app,
                        Blade rendering, and public web entrypoint are all connected properly.
                    </p>

                    <div class="actions">
                        <a class="button" href="{{ url('/') }}">Reload Home</a>
                        <a class="button-secondary" href="https://laravel.com/docs" target="_blank" rel="noreferrer">Laravel Docs</a>
                    </div>
                </section>

                <aside class="panel">
                    <div class="card">
                        <p class="label">What is Live</p>
                        <ul class="stack">
                            <li><strong>View</strong> <span>home.blade.php</span></li>
                            <li><strong>Route</strong> <span>GET /</span></li>
                            <li><strong>Framework</strong> <span>Laravel</span></li>
                        </ul>
                    </div>

                    <div class="card">
                        <p class="label">Next Step</p>
                        <div>Start the local TEST and open the root URL to confirm the page renders in the browser.</div>
                    </div>
                </aside>
            </main>

            <section class="metrics">
                <article class="metric">
                    <strong>Route OK</strong>
                    <p>The homepage responds from the default web routes file.</p>
                </article>
                <article class="metric">
                    <strong>Blade OK</strong>
                    <p>The page content is rendered through Laravel's view layer.</p>
                </article>
                <article class="metric">
                    <strong>Ready</strong>
                    <p>You can now replace this with your real homepage structure when needed.</p>
                </article>
            </section>

            <p class="footer">Built for a quick local smoke test on {{ now()->format('j M Y') }}.</p>
        </div>
    </body>
</html>
