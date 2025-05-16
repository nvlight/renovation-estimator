<?php

use App\Models\User;

it('logs out a user and deletes the token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Logged out']);

    expect($user->tokens)->toBeEmpty();
});

it('fails to logout without authentication', function () {
    $response = $this->postJson('/api/logout');

    $response->assertStatus(401); // Sanctum вернет 401 для неаутентифицированных
});
