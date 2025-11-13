<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'walls' => 'array',
            'walls.*.length' => 'required|integer',  // длина стены, в сантиметрах!
            'walls.*.angle' => 'required|integer|min:-180|max:180',
            'walls.*.is_real' => 'required|integer|min:0|max:1',
        ];
    }
}
