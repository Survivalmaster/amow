<?php

use App\Http\Controllers\Api\DiscordLinkCompletionController;
use App\Http\Controllers\Api\DiscordProfileController;
use App\Http\Controllers\Api\DiscordCommandConfigController;
use App\Http\Controllers\Api\DiscordWpnnController;
use Illuminate\Support\Facades\Route;

Route::post('/discord/link/complete', DiscordLinkCompletionController::class)
    ->name('api.discord.link.complete');

Route::get('/discord/profile/{discordUserId}', DiscordProfileController::class)
    ->name('api.discord.profile.show');

Route::get('/discord/commands', DiscordCommandConfigController::class)
    ->name('api.discord.commands.index');

Route::post('/discord/wpnn', DiscordWpnnController::class)
    ->name('api.discord.wpnn.store');
