<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Http\Resources\V1\ProjectResource;
use App\Http\Resources\V1\RoomResource;
use App\Models\Project;
use App\Models\Room;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Js;

class RoomController extends Controller
{
    use AuthorizesRequests;

    public function index(Project $project, Request $request): JsonResponse
    {
        $rooms = $project
            ->rooms()
            ->orderBy('updated_at', 'desc')
            ->paginate($request->get('per_page', Room::PER_PAGE));
        sleep(1);

        return RoomResource::collection($rooms)->response();
    }

    public function store(StoreRoomRequest $request): JsonResponse
    {
        $room = Room::query()->create([
            'project_id' => $request->project_id,
            'name' => $request->name,
            'description' => $request->description,
            'height' => $request->height,
        ]);
        sleep(1);

        return response()->json(new RoomResource($room), 201);
    }

    public function show(Project $project, Room $room): JsonResponse
    {
        $this->authorize('view', $room);

        return response()->json(new RoomResource($room));
    }

    public function update(Project $project, Room $room, UpdateRoomRequest $request) : JsonResponse
    {
        $this->authorize('update', $room);

        $room->update($request->validated());
        sleep(1);

        return response()->json(new RoomResource($room));
    }

    public function destroy(Project $project, Room $room): JsonResponse
    {
        $this->authorize('delete', $room);

        $room->delete();
        sleep(1);

        return response()->json(['message' => 'Комната удалёна']);
    }
}
