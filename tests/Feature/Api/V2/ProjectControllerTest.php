<?php

use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a paginated list of projects for v2 with transformed fields', function () {
    $user = User::factory()->create();
    Project::factory()->count(15)->for($user)->create();

    $response = $this->actingAs($user)
        ->getJson('/api/v2/project');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                [
                    'id', 'user_id', 'name', 'description', 'created', 'location' => ['name', 'lat', 'lng'],
                ]
            ],
            'links',
            'meta',
        ])
        ->assertJsonCount(Project::PER_PAGE, 'data');
});

it('can show project for v2', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $response = $this->actingAs($user)
        ->getJson("/api/v2/project/{$project->id}");

    $response->assertOk()
        ->assertJsonFragment([
            'user_id'     => $project->user_id,
            'name'        => $project->name,
            'description' => $project->description,
            'created'     => (string) $project->created,
        ])
        ->assertJsonPath('location.lat', $project->latitude !== null ? number_format($project->latitude, 8, '.', '') : null)
        ->assertJsonPath('location.lng', $project->longitude !== null ? number_format($project->longitude, 8, '.', '') : null);
});

it('can store a new project for v2', function () {
    $user = User::factory()->create();

    $data = [
        'name' => 'New Project',
        'description' => 'Cool thing',
        'place_name' => 'Paris',
        'latitude' => 48.8566,
        'longitude' => 2.3522,
    ];

    $response = $this->actingAs($user)
        ->postJson('/api/v2/project', $data);

    $response->assertCreated()
        ->assertJsonPath('name', 'New Project');
});

it('can update a project for v2', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $response = $this->actingAs($user)
        ->patchJson("/api/v2/project/{$project->id}", ['name' => 'Another Name']);

    $response->assertOk()
        ->assertJsonPath('name', 'Another Name');
});

it('can delete a project for v2', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $response = $this->actingAs($user)
        ->deleteJson("/api/v2/project/{$project->id}");

    $response->assertOk()
        ->assertJson(['message' => 'Проект удалён']);

    expect(Project::find($project->id))->toBeNull();
});
