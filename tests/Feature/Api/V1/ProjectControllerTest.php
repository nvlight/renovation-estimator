<?php

use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a paginated list of projects for v1', function () {
    $user = User::factory()->create();
    Project::factory()->count(15)->for($user)->create();

    $response = $this->actingAs($user)
        ->getJson('/api/v1/projects');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                [
                    'id', 'user_id', 'name', 'description', 'created', 'place_name', 'latitude', 'longitude',
                ]
            ],
            'links',
            'meta',
        ])
        ->assertJsonCount(Project::PER_PAGE, 'data');
});

it('can show project for v1', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $response = $this->actingAs($user)
        ->getJson("/api/v1/projects/{$project->id}");

    $response->assertOk()
        ->assertJsonFragment([
            'user_id'     => $project->user_id,
            'name'        => $project->name,
            'description' => $project->description,
            'created'     => (string) $project->created,
            'place_name'  => $project->place_name,
        ])
        ->assertJsonPath('latitude', $project->latitude !== null ? number_format($project->latitude, 8, '.', '') : null)
        ->assertJsonPath('longitude', $project->longitude !== null ? number_format($project->longitude, 8, '.', '') : null);
});

it('can store a new project for v1', function () {
    $user = User::factory()->create();

    $data = [
        'name' => 'Test Project',
        'description' => 'Lorem ipsum',
        'place_name' => 'Berlin',
        'latitude' => 52.52,
        'longitude' => 13.405,
    ];

    $response = $this->actingAs($user)
        ->postJson('/api/v1/projects', $data);

    $response->assertCreated()
        ->assertJsonFragment(['name' => 'Test Project']);

    expect(Project::first()->user_id)->toBe($user->id);
});

it('can update a project for v1', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $response = $this->actingAs($user)
        ->patchJson("/api/v1/projects/{$project->id}", ['name' => 'Updated Name']);

    $response->assertOk()
        ->assertJsonFragment(['name' => 'Updated Name']);
});

it('can delete a project for v1', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $response = $this->actingAs($user)
        ->deleteJson("/api/v1/projects/{$project->id}");

    $response->assertOk()
        ->assertJson(['message' => 'Проект удалён']);

    expect(Project::find($project->id))->toBeNull();
});
