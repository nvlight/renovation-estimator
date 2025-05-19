<?php

use App\Http\Controllers\Api\V2\ProjectController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('v2/projects', ProjectController::class);
});
