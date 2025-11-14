<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomJobRequest;
use App\Http\Resources\V1\RoomJobResource;
use App\Models\Room;
use App\Models\RoomJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomJobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Room $room): JsonResponse
    {
        $jobs = RoomJob::query()->where('room_id', $room->id)->get();
        $result = ['data' => $jobs];

        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoomJobRequest $request): JsonResponse
    {
        $data = $request->validated();
        $roomJob = RoomJob::query()->create($data);

        return response()->json(new RoomJobResource($roomJob), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RoomJobs $roomJobs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomJobs $roomJobs)
    {
        //
    }
}
