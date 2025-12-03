<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomMaterialRequest;
use App\Http\Requests\UpdateRoomMaterialRequest;
use App\Http\Resources\V1\RoomMaterialResource;
use App\Models\Room;
use App\Models\RoomMaterial;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RoomMaterialController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Room $room): JsonResponse
    {
        $this->authorize('viewAny', [RoomMaterial::class, $room]);

//        $userId = Auth::id();
//        $roomMaterials = RoomMaterial::whereHas('room.project.user', function ($query) use ($userId) {
//            $query->where('id', $userId);
//        })
//            ->with('material')
//            ->paginate();

        $roomMaterials = RoomMaterial::query()
            ->where('room_id', $room->id)
            ->paginate();

        return RoomMaterialResource::collection($roomMaterials)->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoomMaterialRequest $request): JsonResponse
    {
        $data = $request->validated();
        $roomMaterial = RoomMaterial::query()->create($data);

        //return response()->json(new RoomMaterialResource::($roomMaterial), 201);
        return RoomMaterialResource::make($roomMaterial)->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(RoomMaterial $roomMaterial)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoomMaterialRequest $request, RoomMaterial $roomMaterial)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room, RoomMaterial $roomMaterial): JsonResponse
    {
        $this->authorize('delete', $roomMaterial);

        $roomMaterial->delete();

        return response()->json(['message' => 'Строительный материал комнаты удален!']);
    }
}
