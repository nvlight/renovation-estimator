<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
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
            // нет надобности проверить эти ID на существование, потому что они уже разрешены сервис контейнером.
            //'project_id' => ['required', 'exists:projects,id'],
            //'room_id' => ['required', 'exists:rooms,id'],
            'name' => ['required', 'string', 'min:3', 'max:155'],
            'description' => ['nullable', 'string', 'max:155'],
            'height' => ['required', 'numeric', 'min:2', 'max:5'],
        ];
    }
}
