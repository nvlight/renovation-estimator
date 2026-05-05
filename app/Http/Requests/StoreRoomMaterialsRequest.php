<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomMaterialsRequest extends FormRequest
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
            'materials' => ['required', 'array', 'min:1'],
            'materials.*.material_id' => 'required|integer|exists:materials,id',
            'materials.*.amount' => 'required|integer|min:1',
            'materials.*.sum' => ['nullable', 'numeric', 'min:0', 'decimal:0,2', 'max:99999999.99'],
            'materials.*.note' => 'nullable|string',
        ];
    }
}
