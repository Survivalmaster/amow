<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CharacterAdminController;
use App\Http\Controllers\Admin\CityAdminController;
use App\Http\Controllers\Admin\FactionAdminController;
use App\Http\Controllers\Admin\ItemAdminController;
use App\Http\Controllers\Admin\LocationAdminController;
use App\Http\Controllers\Admin\MapMarkerAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\DiscordAdminController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DiscordLinkController;
use App\Http\Controllers\FactionController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\WorkController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameController::class, 'home'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [GameController::class, 'dashboard'])->name('dashboard');

    Route::get('/factions', [FactionController::class, 'index'])->name('factions.index');
    Route::post('/factions', [FactionController::class, 'store'])->name('factions.store');

    Route::get('/character/create', [CharacterController::class, 'create'])->name('characters.create');
    Route::post('/character', [CharacterController::class, 'store'])->name('characters.store');

    Route::middleware('character')->group(function () {
        Route::get('/lobby', [GameController::class, 'lobby'])->name('lobby');
        Route::get('/cities/{city:slug}', [CityController::class, 'show'])->name('cities.show');
        Route::get('/locations/{location}', [LocationController::class, 'show'])->name('locations.show');
        Route::post('/locations/{location}/messages', [MessageController::class, 'store'])->name('messages.store');
        Route::post('/locations/{location}/work', [WorkController::class, 'store'])->name('work.store');
        Route::get('/store', [StoreController::class, 'index'])->name('store.index');
        Route::post('/store/purchase', [StoreController::class, 'purchase'])->name('store.purchase');
        Route::get('/profile/game', [CharacterController::class, 'show'])->name('characters.show');
        Route::get('/leaderboards', [LeaderboardController::class, 'index'])->name('leaderboards.index');
        Route::get('/stocks', [MarketController::class, 'index'])->name('market.index');
        Route::post('/stocks/{company}/buy', [MarketController::class, 'buy'])->name('market.buy');
        Route::post('/stocks/{company}/sell', [MarketController::class, 'sell'])->name('market.sell');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/discord-link', [DiscordLinkController::class, 'store'])->name('profile.discord-link.store');
    Route::delete('/profile/discord-link', [DiscordLinkController::class, 'destroy'])->name('profile.discord-link.destroy');

    Route::prefix('admin')->middleware('can:access-admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}', [UserAdminController::class, 'update'])->name('users.update');
        Route::get('/characters', [CharacterAdminController::class, 'index'])->name('characters.index');
        Route::patch('/characters/{character}', [CharacterAdminController::class, 'update'])->name('characters.update');
        Route::get('/factions', [FactionAdminController::class, 'index'])->name('factions.index');
        Route::post('/factions', [FactionAdminController::class, 'store'])->name('factions.store');
        Route::patch('/factions/{faction}', [FactionAdminController::class, 'update'])->name('factions.update');
        Route::get('/cities', [CityAdminController::class, 'index'])->name('cities.index');
        Route::post('/cities', [CityAdminController::class, 'store'])->name('cities.store');
        Route::patch('/cities/{city}', [CityAdminController::class, 'update'])->name('cities.update');
        Route::get('/locations', [LocationAdminController::class, 'index'])->name('locations.index');
        Route::post('/locations', [LocationAdminController::class, 'store'])->name('locations.store');
        Route::patch('/locations/{location}', [LocationAdminController::class, 'update'])->name('locations.update');
        Route::get('/items', [ItemAdminController::class, 'index'])->name('items.index');
        Route::post('/items', [ItemAdminController::class, 'store'])->name('items.store');
        Route::patch('/items/{item}', [ItemAdminController::class, 'update'])->name('items.update');
        Route::get('/map-markers', [MapMarkerAdminController::class, 'index'])->name('map-markers.index');
        Route::post('/map-markers', [MapMarkerAdminController::class, 'store'])->name('map-markers.store');
        Route::patch('/map-markers/{mapMarker}', [MapMarkerAdminController::class, 'update'])->name('map-markers.update');
        Route::delete('/map-markers/{mapMarker}', [MapMarkerAdminController::class, 'destroy'])->name('map-markers.destroy');
        Route::get('/discord', [DiscordAdminController::class, 'index'])->name('discord.index');
        Route::patch('/discord', [DiscordAdminController::class, 'update'])->name('discord.update');
    });
});

require __DIR__.'/auth.php';
