<?php

namespace App\Providers;

use App\Models\Character;
use App\Policies\CharacterPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Character::class, CharacterPolicy::class);
        Gate::define('access-admin', fn ($user) => $user->is_admin);
    }
}
