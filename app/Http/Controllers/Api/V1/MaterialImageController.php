<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialImageRequest;
use App\Http\Requests\UpdateMarerialImageRequest;
use App\Http\Resources\V1\MaterialImageResource;
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

        return MaterialImageResource::collection($materials)->response();
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

    public function toLeft(MaterialImage $materialImage): JsonResponse
    {
        try {
            // Находим текущий элемент с блокировкой для избежания race condition
            $current = MaterialImage::where('id', $materialImage->id)
                ->lockForUpdate()
                ->first();

            // Ищем левого соседа (элемент с меньшим sort)
            $leftNeighbour = MaterialImage::where('material_id', $current->material_id)
                ->where('sort', '<', $current->sort)
                ->orderBy('sort', 'desc')
                ->lockForUpdate()
                ->first();

            if (!$leftNeighbour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Элемент уже находится на крайней левой позиции'
                ], 400);
            }

            // Меняем sort местами
            $currentSort = $current->sort;
            $current->sort = $leftNeighbour->sort;
            $leftNeighbour->sort = $currentSort;

            $current->save();
            $leftNeighbour->save();

            return response()->json([
                'success' => true,
                'message' => 'Элемент сдвинут влево'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при сдвиге элемента: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toRight(MaterialImage $materialImage): JsonResponse
    {
        try {
            // Находим текущий элемент с блокировкой
            $current = MaterialImage::where('id', $materialImage->id)
                ->lockForUpdate()
                ->first();

            // Ищем правого соседа (элемент с большим sort)
            $rightNeighbour = MaterialImage::where('material_id', $current->material_id)
                ->where('sort', '>', $current->sort)
                ->orderBy('sort', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$rightNeighbour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Элемент уже находится на крайней правой позиции'
                ], 400);
            }

            // Меняем sort местами
            $currentSort = $current->sort;
            $current->sort = $rightNeighbour->sort;
            $rightNeighbour->sort = $currentSort;

            $current->save();
            $rightNeighbour->save();

            return response()->json([
                'success' => true,
                'message' => 'Элемент сдвинут вправо'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при сдвиге элемента: ' . $e->getMessage()
            ], 500);
        }
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
