<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
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
            'parent_id' => ['nullable', 'integer', 'min:0'],

            'title' => ['required', 'string', 'max:255', 'min:3'],
            'description' => ['nullable', 'string', 'max:555'],
            'product_url' => ['nullable', 'string', 'max:255'],

            'price' => ['nullable', 'numeric', 'min:0', 'decimal:0,2', 'max:99999999.99'],

            'product_code' => ['nullable', 'integer'],
            'is_free' => 'nullable|boolean',

            'characteristics' => ['nullable', 'array'],
            'advantages' => ['nullable', 'array'],
            'packaging_info' => ['nullable', 'array'],

            'brand' => ['nullable', 'string:max:111'],
            'producing_country' => ['nullable', 'string:max:111'],
        ];
    }
}
