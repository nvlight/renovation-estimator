<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomWallRequest;
use App\Http\Requests\UpdateRoomWallRequest;
use App\Http\Resources\V1\RoomWallResource;
use App\Models\Room;
use App\Models\RoomWall;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomWallController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Room $room, Request $request): JsonResponse
    {
        $roomWalls = $room
            ->room_walls()
            ->orderBy('updated_at', 'desc')
            ->paginate($request->get('per_page', RoomWall::PER_PAGE));
        sleep(1);

        return RoomWallResource::collection($roomWalls)->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Room $room, StoreRoomWallRequest $request): JsonResponse
    {
        $roomWall = RoomWall::query()->create([
            'room_id' => $room->id,
            'length' => $request->length,
            'angle' => $request->angle,
            'order' => $request->order,
        ]);
        sleep(1);

        return response()->json(new RoomWallResource($roomWall), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room, RoomWall $roomWall): JsonResponse
    {
        $this->authorize('view', $roomWall);

        return response()->json(new RoomWallResource($roomWall));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoomWallRequest $request, Room $room, RoomWall $roomWall): JsonResponse
    {
        $this->authorize('update', $roomWall);

        $roomWall->update($request->validated());
        sleep(1);

        return response()->json(new RoomWallResource($roomWall));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room, RoomWall $roomWall): JsonResponse
    {
        $this->authorize('delete', $roomWall);

        $roomWall->delete();
        sleep(1);

        return response()->json(['message' => 'Стена комнаты удалёна']);
    }
}
