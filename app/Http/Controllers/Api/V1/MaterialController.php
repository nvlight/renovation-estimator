<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Http\Resources\V1\MaterialResource;
use App\Models\Material;
use Illuminate\Http\JsonResponse;

class MaterialController extends Controller
{
    public function index(): JsonResponse
    {
        // todo: авторизация, может нужно добавить middleware с полем user.role === ADMIN

        $materials = Material::query()
            ->orderBy('title')
            ->paginate();

        return MaterialResource::collection($materials)->response();
    }

    public function store(StoreMaterialRequest $request): JsonResponse
    {
        $data = $request->validated();

        Material::query()->create($data);

        return response()->json(['success' => 1], 201);
    }

    public function show(Material $material): JsonResponse
    {
        return response()->json(new MaterialResource($material));
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        //
    }

    public function destroy(Material $material): JsonResponse
    {
        $material->delete();

        return response()->json(['message' => 'Материал удалён']);
    }
}
