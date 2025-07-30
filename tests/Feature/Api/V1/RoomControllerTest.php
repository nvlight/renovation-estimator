<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Project;
use App\Models\Room;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

it('returns paginated room list (v1)', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(); // Создаем один проект
    Room::factory()->count(9)->for($project, 'project')->create(); // Явно указываем имя связи

    $response = $this->actingAs($user)
        ->getJson("/api/v1/project/{$project->id}/room");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                [
                    'id', 'project_id', 'name', 'description', 'height',
                ]
            ],
            'links',
            'meta',
        ])
        ->assertJsonCount(Room::PER_PAGE, 'data');
});

it('can show room (v1)', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(); // Создаем один проект
    $room = Room::factory()->for($project, 'project')->create(['height' => "2.80"]); // Явно указываем имя связи

    $response = $this->actingAs($user)
        ->getJson("/api/v1/project/{$project->id}/room/{$room->id}");

    $response->assertOk()
        ->assertJsonFragment([
            'id' => $room->id,
            'project_id' => $room->project_id,
            'name' => $room->name,
            'description' => $room->description,
            'height' => (string) $room->height,
        ]);
});

it('can store room (v1)', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $data = [
        'project_id' => $project->id,
        'name' => 'First cool room',
        'description' => 'its my first room in this test',
        'height' => 2.79,
    ];

    $response = $this->actingAs($user)
        ->postJson("/api/v1/project/{$project->id}/room", $data);

    $response->assertCreated()
        ->assertJsonFragment([
            'project_id' => $project->id,
            'name' => 'First cool room',
            'description' => $data['description'],
            'height' => $data['height'],
        ]);
});

it('can update room (v1)', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $room = Room::factory()->for($project, 'project')->create(['height' => "2.80"]); // Явно указываем имя связи

    $response = $this->actingAs($user)
        ->patchJson("/api/v1/project/{$project->id}/room/{$room->id}", [
            'name' => 'This is new room!',
            'description' => 'its my first room in this test',
            'height' => 2.79,
        ]);

    $response->assertOk()
        ->assertJsonPath('name', 'This is new room!');
});

it('can delete room (v1)', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $room = Room::factory()->for($project, 'project')->create();

    $response = $this->actingAs($user)
        ->deleteJson("/api/v1/project/{$project->id}/room/{$room->id}");

    $response->assertOk()
        ->assertJson(['message' => 'Комната удалёна']);

    expect(Room::query()->find($room->id))->toBeNull();
});
