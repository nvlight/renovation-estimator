<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

it('registers a new user and returns a token', function () {
    Event::fake();

    $response = $this->postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['user', 'token'])
        ->assertJsonFragment(['name' => 'John Doe', 'email' => 'john@example.com']);

    $user = User::query()->where('email', 'john@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->tokens)->not->toBeEmpty();

    Event::assertDispatched(Registered::class, fn ($event) => $event->user->id === $user->id);
});

it('fails to register with invalid data', function () {
    $response = $this->postJson('/api/register', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'short',
        'password_confirmation' => 'different',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});
