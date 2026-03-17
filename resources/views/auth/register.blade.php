<x-guest-layout>
    <div class="grid gap-6 xl:grid-cols-[1.05fr_minmax(0,0.95fr)] xl:items-stretch">
        <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-[linear-gradient(145deg,rgba(29,52,39,0.96),rgba(8,15,11,0.94))] p-6 shadow-2xl shadow-black/35 sm:p-8">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(194,168,79,0.22),transparent_32%),radial-gradient(circle_at_bottom_right,rgba(126,173,89,0.18),transparent_28%)]"></div>
            <div class="relative">
                <p class="inline-flex rounded-full border border-[#c2a84f]/30 bg-black/15 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#f4d77a]">
                    New Recruit Intake
                </p>
                <h1 class="mt-5 font-['Teko'] text-4xl uppercase leading-none tracking-[0.08em] text-[#f4ecd0] sm:text-5xl lg:text-6xl">
                    Enter the War for Plastica
                </h1>
                <p class="mt-4 max-w-xl text-sm leading-6 text-white/72 sm:text-base">
                    Create your AMOW account, build your character, choose your path, and get your Discord linked so the bot can recognize you across the world.
                </p>

                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-[1.4rem] border border-white/10 bg-black/20 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/42">1</p>
                        <p class="mt-2 font-['Teko'] text-2xl uppercase tracking-[0.08em] text-white">Register</p>
                        <p class="mt-1 text-xs leading-5 text-white/58">Create your AMOW account and secure your login.</p>
                    </div>
                    <div class="rounded-[1.4rem] border border-white/10 bg-black/20 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/42">2</p>
                        <p class="mt-2 font-['Teko'] text-2xl uppercase tracking-[0.08em] text-white">Link Discord</p>
                        <p class="mt-1 text-xs leading-5 text-white/58">A link prompt will appear after login with your one-time bot code.</p>
                    </div>
                    <div class="rounded-[1.4rem] border border-white/10 bg-black/20 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/42">3</p>
                        <p class="mt-2 font-['Teko'] text-2xl uppercase tracking-[0.08em] text-white">Deploy</p>
                        <p class="mt-1 text-xs leading-5 text-white/58">Create your character and start building wealth, power, and territory.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-[linear-gradient(180deg,rgba(14,25,18,0.95),rgba(8,14,11,0.94))] p-6 shadow-2xl shadow-black/35 sm:p-8">
            <div>
                <p class="font-['Teko'] text-4xl uppercase tracking-[0.08em] text-[#f4ecd0] sm:text-5xl">Create Account</p>
                <p class="mt-2 text-sm leading-6 text-white/58">
                    Mobile friendly, quick to complete, and ready to hand you straight into the Discord linking flow.
                </p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
                @csrf

                <label class="grid gap-2 text-sm text-white/72">
                    <span class="uppercase tracking-[0.18em] text-white/45">Display Name</span>
                    <input id="name" class="w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-base text-[#f4ecd0] outline-none transition placeholder:text-white/28 focus:border-[#7ead59]/60 focus:ring-0" type="text" name="name" value="{{ old('name') }}" placeholder="Survivalmaster" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </label>

                <label class="grid gap-2 text-sm text-white/72">
                    <span class="uppercase tracking-[0.18em] text-white/45">Email Address</span>
                    <input id="email" class="w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-base text-[#f4ecd0] outline-none transition placeholder:text-white/28 focus:border-[#7ead59]/60 focus:ring-0" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </label>

                <div class="grid gap-5 lg:grid-cols-2">
                    <label class="grid gap-2 text-sm text-white/72">
                        <span class="uppercase tracking-[0.18em] text-white/45">Password</span>
                        <input id="password" class="w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-base text-[#f4ecd0] outline-none transition placeholder:text-white/28 focus:border-[#7ead59]/60 focus:ring-0" type="password" name="password" placeholder="Create a secure password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </label>

                    <label class="grid gap-2 text-sm text-white/72">
                        <span class="uppercase tracking-[0.18em] text-white/45">Confirm Password</span>
                        <input id="password_confirmation" class="w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-base text-[#f4ecd0] outline-none transition placeholder:text-white/28 focus:border-[#7ead59]/60 focus:ring-0" type="password" name="password_confirmation" placeholder="Repeat your password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                    </label>
                </div>

                <div class="rounded-[1.4rem] border border-white/10 bg-black/20 px-4 py-3 text-sm text-white/60">
                    After sign-up, we’ll prompt you to link Discord with a one-time code so the AMOW bot can connect to your account.
                </div>

                <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                    <a class="text-sm font-semibold text-white/60 transition hover:text-[#f4ecd0]" href="{{ route('login') }}">
                        Already registered?
                    </a>

                    <button class="inline-flex items-center justify-center rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c] transition hover:bg-[#92c46a]">
                        Register
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-guest-layout>
