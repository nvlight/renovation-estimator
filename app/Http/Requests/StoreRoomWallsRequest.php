<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class StoreRoomWallsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'walls' => 'nullable|array',
            'walls.*.length' => 'required|integer',  // длина стены:
            'walls.*.angle' => 'required|integer|min:-180|max:180',  // Угол: -180..180° (пример, скорректируйте)
            'walls.*.is_real' => 'required|integer|min:0|max:1',  // Флаг: 0 или 1 (из вашего примера)
            // Добавьте другие правила для запроса, если нужно
        ];
    }

    /**
     * Prepare the data for validation.
     * @throws ValidationException
     */
    protected function prepareForValidation(): void
    {
        $wallsInput = $this->input('walls', '');  // Получаем строку из поля 'walls' (пустую по умолчанию)

        if (empty($wallsInput)) {
            $this->merge(['walls' => []]);
            return;
        }

        // Парсим многострочную строку в массив ассоциативных массивов
        $parsedWalls = Collection::make(explode("\n", trim($wallsInput)))
            ->map(function ($line) {
                $line = trim($line);
                if (empty($line)) {
                    return null;
                }

                // Проверяем формат: ровно три целых числа (с возможными минусами), разделённые пробелами
                if (!preg_match('/^(-?\d+)\s+(-?\d+)\s+(-?\d+)$/u', $line)) {  // /u для UTF-8, если нужно
                    throw ValidationException::withMessages([
                        'walls' => 'Неверный формат данных в поле walls. Каждая строка должна содержать ровно три целых числа, разделённых пробелами (например: "310 90 1").'
                    ]);
                }

                $parts = preg_split('/\s+/', $line, 3);  // Разделяем по пробелам, max 3 части
                return [
                    'length' => (int) $parts[0],
                    'angle' => (int) $parts[1],
                    'is_real' => (int) $parts[2],
                ];
            })
            ->filter()  // Убираем null (пустые строки)
            ->values()  // Переиндексируем
            ->toArray();

        $this->merge(['walls' => $parsedWalls]);
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        return parent::validated($key, $default);
    }
}
