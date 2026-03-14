<?php

use App\Http\Controllers\Api\DiscordLinkCompletionController;
use App\Http\Controllers\Api\DiscordProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/discord/link/complete', DiscordLinkCompletionController::class)
    ->name('api.discord.link.complete');

Route::get('/discord/profile/{discordUserId}', DiscordProfileController::class)
    ->name('api.discord.profile.show');
