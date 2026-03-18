<?php

namespace App\Providers;

use App\Models\Character;
use App\Models\GameEvent;
use App\Policies\CharacterPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

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
        Gate::define('access-admin', fn ($user) => $user->loadMissing('permissions')->canAccessAdmin());

        view()->composer('layouts.app', function (View $view): void {
            $activeEvents = collect();

            if (
                auth()->check()
                && ! request()->routeIs('admin.*')
                && Schema::hasTable('game_events')
            ) {
                $characterFactionId = auth()->user()?->character?->faction_id;

                $activeEvents = GameEvent::query()
                    ->where('is_enabled', true)
                    ->where(function ($query) use ($characterFactionId) {
                        $query->whereNull('faction_id');

                        if ($characterFactionId) {
                            $query->orWhere('faction_id', $characterFactionId);
                        }
                    })
                    ->latest()
                    ->get();
            }

            $view->with('activeGameEvents', $activeEvents);
        });
    }
}
