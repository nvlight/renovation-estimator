<?php

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

it('resets a user password', function () {
    $user = User::factory()->create(['email' => 'john@example.com']);
    $token = Password::createToken($user);

    Event::fake();

    $response = $this->postJson('/api/reset-password', [
        'token' => $token,
        'email' => 'john@example.com',
        'password' => 'newpassword',
        'password_confirmation' => 'newpassword',
    ]);

    $response->assertStatus(200)
        ->assertJson(['status' => 'Your password has been reset.']);

    $updatedUser = $user->fresh();
    expect(Hash::check('newpassword', $updatedUser->password))->toBeTrue();
    Event::assertDispatched(PasswordReset::class);
});

it('fails to reset password with invalid token', function () {
    $user = User::factory()->create(['email' => 'john@example.com']);

    $response = $this->postJson('/api/reset-password', [
        'token' => 'invalid-token',
        'email' => 'john@example.com',
        'password' => 'newpassword',
        'password_confirmation' => 'newpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});
