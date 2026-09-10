<?php

use App\Http\Controllers\Api\DiscordLinkCompletionController;
use App\Http\Controllers\Api\DiscordBankController;
use App\Http\Controllers\Api\DiscordBulkDefaultRankPlanController;
use App\Http\Controllers\Api\DiscordChangelogController;
use App\Http\Controllers\Api\DiscordJobChangeController;
use App\Http\Controllers\Api\DiscordJobsController;
use App\Http\Controllers\Api\DiscordProfileController;
use App\Http\Controllers\Api\DiscordCommandConfigController;
use App\Http\Controllers\Api\DiscordPrayerController;
use App\Http\Controllers\Api\DiscordRoleSyncController;
use App\Http\Controllers\Api\DiscordStoreController;
use App\Http\Controllers\Api\DiscordStorePurchaseController;
use App\Http\Controllers\Api\DiscordWorkController;
use App\Http\Controllers\Api\DiscordWpnnController;
use Illuminate\Support\Facades\Route;

Route::post('/discord/link/complete', DiscordLinkCompletionController::class)
    ->name('api.discord.link.complete');

Route::get('/discord/profile/{discordUserId}', DiscordProfileController::class)
    ->name('api.discord.profile.show');

Route::get('/discord/bank/{discordUserId}', DiscordBankController::class)
    ->name('api.discord.bank.show');

Route::post('/discord/work', DiscordWorkController::class)
    ->name('api.discord.work.store');

Route::get('/discord/jobs/{discordUserId}', DiscordJobsController::class)
    ->name('api.discord.jobs.index');

Route::post('/discord/jobs/change', DiscordJobChangeController::class)
    ->name('api.discord.jobs.change');

Route::get('/discord/store/{discordUserId}', DiscordStoreController::class)
    ->name('api.discord.store.index');

Route::post('/discord/store/purchase', DiscordStorePurchaseController::class)
    ->name('api.discord.store.purchase');

Route::get('/discord/commands', DiscordCommandConfigController::class)
    ->name('api.discord.commands.index');

Route::post('/discord/wpnn', DiscordWpnnController::class)
    ->name('api.discord.wpnn.store');

Route::post('/discord/pray', DiscordPrayerController::class)
    ->name('api.discord.pray.store');

Route::post('/discord/roles/sync', DiscordRoleSyncController::class)
    ->name('api.discord.roles.sync');

Route::post('/discord/ranks/default-plan', DiscordBulkDefaultRankPlanController::class)
    ->name('api.discord.ranks.default-plan');

Route::get('/discord/changelogs/pending', [DiscordChangelogController::class, 'pending'])
    ->name('api.discord.changelogs.pending');

Route::post('/discord/changelogs/{changelog}/sent', [DiscordChangelogController::class, 'markSent'])
    ->name('api.discord.changelogs.sent');
