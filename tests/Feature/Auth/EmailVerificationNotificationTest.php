<?php

use App\Models\User;

it('sends an email verification notification', function () {
    $user = User::factory()->unverified()->create();
    $token = $user->createToken('auth-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/email/verification-notification');

    $response->assertStatus(200)
        ->assertJson(['status' => 'verification-link-sent']);
});

it('returns already verified message if email is verified', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $token = $user->createToken('auth-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/email/verification-notification');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Email already verified']);
});
