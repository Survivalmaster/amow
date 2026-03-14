<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Discord Link
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Link your Discord account so the bot can identify your AMOW profile and send game updates.
        </p>
    </header>

    <div class="mt-6 space-y-4 text-sm text-gray-700 dark:text-gray-300">
        @if ($user->discord_user_id)
            <p>
                Linked as <span class="font-semibold">{{ $user->discord_username }}</span>
                @if ($user->discord_linked_at)
                    on {{ $user->discord_linked_at->timezone(config('app.timezone'))->format('j M Y H:i') }}
                @endif
            </p>

            <form method="post" action="{{ route('profile.discord-link.destroy') }}">
                @csrf
                @method('delete')

                <x-danger-button>
                    Unlink Discord
                </x-danger-button>
            </form>
        @else
            <p>No Discord account is linked yet.</p>

            @if ($user->discord_link_token && $user->discord_link_token_expires_at?->isFuture())
                <div class="rounded-md border border-sky-200 bg-sky-50 p-4 text-sky-900 dark:border-sky-800 dark:bg-sky-950 dark:text-sky-100">
                    <p class="font-semibold tracking-[0.2em]">{{ $user->discord_link_token }}</p>
                    <p class="mt-2 text-xs">
                        Expires at {{ $user->discord_link_token_expires_at->timezone(config('app.timezone'))->format('j M Y H:i') }}.
                    </p>
                    <p class="mt-2 text-xs">
                        Use this code with the Discord bot command you wire up, for example <code>/link code:{{ $user->discord_link_token }}</code>.
                    </p>
                </div>
            @endif

            <form method="post" action="{{ route('profile.discord-link.store') }}">
                @csrf

                <x-primary-button>
                    {{ $user->discord_link_token && $user->discord_link_token_expires_at?->isFuture() ? 'Refresh link code' : 'Generate link code' }}
                </x-primary-button>
            </form>
        @endif

        @if (session('status') === 'discord-link-issued')
            <p class="text-sm font-medium text-green-600 dark:text-green-400">
                A fresh Discord link code has been generated.
            </p>
        @endif

        @if (session('status') === 'discord-unlinked')
            <p class="text-sm font-medium text-green-600 dark:text-green-400">
                Discord has been unlinked from this account.
            </p>
        @endif
    </div>
</section>
