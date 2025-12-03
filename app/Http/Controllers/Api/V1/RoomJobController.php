<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomJobRequest;
use App\Http\Resources\V1\RoomJobResource;
use App\Models\Room;
use App\Models\RoomJob;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomJobController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Room $room): JsonResponse
    {
        $this->authorize('viewAny', RoomJob::class);

        // todo: эта ярунда не будет работать правильным образом, исправить!
        $jobs = RoomJob::query()->where('room_id', $room->id)->get();
        $result = ['data' => $jobs];

        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoomJobRequest $request): JsonResponse
    {
        $this->authorize('create', RoomJob::class);

        $data = $request->validated();
        $roomJob = RoomJob::query()->create($data);

        return response()->json(new RoomJobResource($roomJob), 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room, RoomJob $roomJob): JsonResponse
    {
        $this->authorize('delete', $roomJob);

        try {
            $success = $roomJob->delete();
            return response()->json([
                'success' => $success,
                'message' => 'Room job deleted successfully.',
                //'item' => $roomJob,
            ]);
        } catch (\Exception $e) {
            logger()->error('Failed to delete $roomJob model', [
                'model_id' => $roomJob->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to delete the resource'
            ], 500);
        }
    }
}
