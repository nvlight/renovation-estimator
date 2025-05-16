<?php

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;

it('sends a password reset link', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'john@example.com']);

    $response = $this->postJson('/api/forgot-password', [
        'email' => 'john@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson(['status' => 'We have emailed your password reset link.']);

    Notification::assertSentTo(
        $user,
        ResetPasswordNotification::class
    );
});

it('fails to send reset link for non-existent email', function () {
    $response = $this->postJson('/api/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});
