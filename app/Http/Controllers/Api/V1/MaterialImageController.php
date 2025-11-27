<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialImageRequest;
use App\Http\Requests\UpdateMarerialImageRequest;
use App\Http\Resources\V1\MaterialResource;
use App\Models\MaterialImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class MaterialImageController extends Controller
{
    public function index(): JsonResponse
    {
        // todo: авторизация, может нужно добавить middleware с полем user.role === ADMIN

        $materials = MaterialImage::query()
            ->orderBy('name')
            ->paginate();

        return MaterialResource::collection($materials)->response();
    }

    public function store(StoreMaterialImageRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('name')) {
            $path = $request->file('name')->store('materials', 'public');
            $validated['name'] = $path;
        }

        MaterialImage::query()->create($validated);

        return response()->json(['success' => 1], 201);
    }

    public function show(MaterialImage $materialImage)
    {
        //
    }

    public function update(UpdateMarerialImageRequest $request, MaterialImage $materialImage)
    {
        //
    }

    public function destroy(MaterialImage $materialImage): JsonResponse
    {
        try {
            // Получаем путь к файлу перед удалением записи
            $imagePath = $materialImage->name;

            // Удаляем запись из базы данных
            $materialImage->delete();

            // Удаляем физический файл изображения
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json(['message' => 'Изображение материала удалено']);

        } catch (\Exception $e) {
            // Логируем ошибку, если нужно
            logger()->error('Ошибка при удалении изображения материала: ' . $e->getMessage());

            return response()->json([
                'message' => 'Произошла ошибка при удалении изображения'
            ], 500);
        }
    }
}
