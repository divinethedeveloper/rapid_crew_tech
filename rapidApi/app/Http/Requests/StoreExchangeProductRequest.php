<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExchangeProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'                  => ['required'],
            'price'                 => ['required', 'numeric'],
            'short_description'     => ['required'],
            'images' => ['required', 'array'],
            'images.*' => ['required', 'file', 'max:1048'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'At least one product image is required'
        ];
    }

    public function attributes()
    {
        return [
            'images.*' => 'images',
        ];
    }
}
