<?php

use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\RoomWallController;
use Illuminate\Support\Facades\Route;

// test string for push - v2
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('v1/project', ProjectController::class);
    Route::apiResource('v1/project/{project}/rooms', RoomController::class);
    Route::apiResource('v1/{room}/roomWalls', RoomWallController::class);
});
