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
        <div class="min-h-screen bg-[rgba(4,8,6,0.35)]">
            @include('layouts.navigation')

            @isset($header)
                <header class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
                    <div class="rounded-[2rem] border border-white/10 bg-white/5 px-6 py-5 shadow-2xl shadow-black/30 backdrop-blur">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-[#7ead59]/35 bg-[#7ead59]/10 px-4 py-3 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-[#c65b3f]/35 bg-[#c65b3f]/10 px-4 py-3 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
