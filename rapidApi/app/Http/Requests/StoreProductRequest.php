<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Category;


class StoreProductRequest extends FormRequest
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
            'name'                  => ['required', 'unique:products,name'],
            'price'                 => ['required', 'numeric'],
            'quantity'              => ['required', 'integer'],
            'short_description'     => ['required'],
            'categories'            => ['required', function ($attr, $value, $fail) {

                $categoryIds = explode(' ', trim($value));
                $categoryIds = array_filter($categoryIds);
                $categoriesCount = Category::whereIn('id', $categoryIds)->count();
                $check = count($categoryIds) == $categoriesCount;

                if (!$check) {
                    $fail(" A selected category no longer exists ");
                }

                return $check;
            }],
            'images' => ['required'],
            'images.*' => ['required', 'image', 'max:5048'],
            'status' => ['required']
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'At least one product image is required'
        ];
    }
}
