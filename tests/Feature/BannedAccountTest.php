<?php

use App\Models\User;

test('banned users are redirected to the banned page from authenticated routes', function () {
    $user = User::factory()->create([
        'banned_at' => now(),
        'banned_reason' => 'Repeated severe rule violations.',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('banned'));
});

test('banned page shows the ban reason and appeal guidance', function () {
    $user = User::factory()->create([
        'banned_at' => now(),
        'banned_reason' => 'Repeated severe rule violations.',
    ]);

    $this->actingAs($user)
        ->get(route('banned'))
        ->assertOk()
        ->assertSee('Account Banned')
        ->assertSee('Repeated severe rule violations.')
        ->assertSee('If you think the ban is incorrect or you would like to appeal this, query this in our discord.')
        ->assertSee('https://discord.gg/vC3hpcfRcq');
});
