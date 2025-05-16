<?php

use App\Models\User;

it('logs in a user and returns a token', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['user', 'token'])
        ->assertJsonFragment(['email' => 'john@example.com']);

    expect($user->tokens)->not->toBeEmpty();
});

it('fails to login with wrong credentials', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'wrong',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email'); // Предполагается, что `authenticate()` кидает ошибку валидации
});
