<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

it('verifies an email', function () {
    $user = User::factory()->unverified()->create();
    $token = $user->createToken('auth-token')->plainTextToken;

    Event::fake();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->getJson($url);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Email verified']);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    Event::assertDispatched(Verified::class);
});

it('returns already verified message if email is verified', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $token = $user->createToken('auth-token')->plainTextToken;

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->getJson($url);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Email already verified']);
});
