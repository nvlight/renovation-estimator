<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomMaterialRequest;
use App\Http\Requests\StoreRoomMaterialsRequest;
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
            ->paginate(RoomMaterial::PER_PAGE);

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
     * Сохранение пачки строительных материалов
     * @param StoreRoomMaterialsRequest $request
     * @param Room $room
     * @return JsonResponse
     */
    public function bulkStore(StoreRoomMaterialsRequest $request, Room $room): JsonResponse
    {
        $data = $request->validated();

        // Добавляем room_id ко всем материалам
//        $materialsToCreate = collect($data['materials'])->map(function ($item) use ($room) {
//            return [
//                'room_id'      => $room->id,
//                'material_id'  => $item['material_id'],
//                'amount'       => $item['amount'],
//                'sum'          => $item['sum'],
//                // 'price'     => $item['price'] ?? null,   // если нужно
//                'created_at'   => now(),
//                'updated_at'   => now(),
//            ];
//        })->toArray();
        //RoomMaterial::query()->insert($materialsToCreate);
//        return response()->json(['success' => 1])->setStatusCode(201);

        // Если нужно вернуть созданные модели с отношениями — лучше использовать createMany + fresh
        $roomMaterials = $room->room_materials()->createMany(
            collect($data['materials'])->map(fn($item) => [
                'material_id' => $item['material_id'],
                'amount'      => $item['amount'],
                'sum'         => $item['sum'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ])->toArray()
        );

        return RoomMaterialResource::collection($roomMaterials)
            ->response()
            ->setStatusCode(201);
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
