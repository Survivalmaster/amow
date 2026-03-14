<?php

use App\Http\Controllers\Api\DiscordLinkCompletionController;
use Illuminate\Support\Facades\Route;

Route::post('/discord/link/complete', DiscordLinkCompletionController::class)
    ->name('api.discord.link.complete');
