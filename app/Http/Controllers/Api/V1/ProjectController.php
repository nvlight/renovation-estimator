<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\V1\ProjectResource;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $projects = Auth::user()
            ->projects()
            ->orderBy('created', 'desc')
            ->paginate($request->get('per_page', Project::PER_PAGE));
        sleep(1);

        return ProjectResource::collection($projects)->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::query()->create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
            'place_name' => $request->place_name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'created' => now(),
        ]);
        sleep(1);

        return response()->json(new ProjectResource($project), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return response()->json(new ProjectResource($project));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project->update($request->validated() + ['created' => now()]);
        sleep(1);

        return response()->json(new ProjectResource($project));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();
        sleep(1);

        return response()->json(['message' => 'Проект удалён']);
    }
}
