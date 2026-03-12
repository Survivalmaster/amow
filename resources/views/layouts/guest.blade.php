<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Army Men of War Roleplay App') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=rajdhani:500,600,700|teko:500,600,700&display=swap" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col items-center justify-center bg-[linear-gradient(180deg,#0f1b15_0%,#07100c_60%,#030705_100%)] px-4 py-8 text-[#f4ecd0]">
            <div>
                <a href="/">
                    <div class="flex h-20 w-20 items-center justify-center rounded-3xl border border-[#7ead59]/40 bg-[#7ead59]/10 font-['Teko'] text-5xl tracking-[0.18em] text-[#7ead59]">AM</div>
                </a>
            </div>

            <div class="mt-6 w-full overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 px-6 py-5 shadow-2xl shadow-black/30 sm:max-w-md">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
