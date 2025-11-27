<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialImageRequest extends FormRequest
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
            'material_id' => 'required|integer|exists:materials,id',
            'name' => [
                'required',
                'image' ,
                'mimes:jpeg,png,jpg,webp',
                'max:5120',
                'dimensions:min_width=250,min_height=250,max_width=1200,max_height=800' // max_width=560,max_height=504
            ],
        ];
    }
}
