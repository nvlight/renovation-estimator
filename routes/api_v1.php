<?php

use App\Http\Controllers\Api\V1\MaterialController;
use App\Http\Controllers\Api\V1\MaterialImageController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\RoomJobController;
use App\Http\Controllers\Api\V1\RoomMaterialController;
use App\Http\Controllers\Api\V1\RoomWallController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('v1/project', ProjectController::class);
    Route::apiResource('v1/project/{project}/rooms', RoomController::class);

    Route::get('v1/roomWalls/{room}', [RoomWallController::class, 'index']);
    Route::post('v1/roomWalls/{room}', [RoomWallController::class, 'store']);

    Route::prefix('v1/room/{room}')->group(function () {
        Route::apiResource('roomJob', RoomJobController::class);
        Route::apiResource('roomMaterial', RoomMaterialController::class);
    });

    Route::apiResource('v1/material', MaterialController::class);

    Route::apiResource('v1/material_image', MaterialImageController::class);
    Route::patch('v1/material_image/{material_image}/to_left', [MaterialImageController::class, 'toLeft'])
        ->name('material_image.to_left');
    Route::patch('v1/material_image/{material_image}/to_right', [MaterialImageController::class, 'toRight'])
        ->name('material_image.to_right');
});
