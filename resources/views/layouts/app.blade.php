<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Army Men of War Roleplay App') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=rajdhani:500,600,700|teko:500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(126,173,89,0.14),_transparent_30%),linear-gradient(180deg,_#102017_0%,_#07100c_55%,_#040806_100%)] font-sans antialiased text-[#f4ecd0]">
        <div class="min-h-screen bg-[rgba(4,8,6,0.35)]">
            <div class="lg:grid lg:grid-cols-[280px_minmax(0,1fr)]">
                @include('layouts.navigation')

                <div class="min-w-0">
                    @isset($header)
                        <header class="px-4 pt-8 sm:px-6 lg:px-8">
                            <div class="rounded-[2rem] border border-white/10 bg-white/5 px-6 py-5 shadow-2xl shadow-black/30 backdrop-blur">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <main class="px-4 py-8 sm:px-6 lg:px-8">
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
            </div>
        </div>
    </body>
</html>
