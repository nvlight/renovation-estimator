<?php

use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\RoomJobController;
use App\Http\Controllers\Api\V1\RoomWallController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('v1/project', ProjectController::class);
    Route::apiResource('v1/project/{project}/rooms', RoomController::class);

    Route::get('v1/roomWalls/{room}', [RoomWallController::class, 'index']);
    Route::post('v1/roomWalls/{room}', [RoomWallController::class, 'store']);

    Route::apiResource('v1/room/{room}/room-job', RoomJobController::class);
});
