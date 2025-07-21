<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomWallRequest extends FormRequest
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
            //'room_id' => ['required', 'exists:room,id'],
            'length' => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'angle' => ['required', 'integer', 'min:-360', 'max:360'],
            'order' => ['required', 'integer', 'min:0'],
        ];
    }
}
