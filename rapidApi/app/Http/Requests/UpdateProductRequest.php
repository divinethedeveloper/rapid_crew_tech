<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'name'                  => [Rule::unique('products', 'name')->ignore($this->product->id)],
            'price'                 => ['numeric'],
            'quantity'              => ['integer'],
            'categories'            => [function ($attr, $value, $fail) {

                $categoryIds = explode(' ', trim($value));
                $categoryIds = array_filter($categoryIds);
                $categoriesCount = Category::whereIn('id', $categoryIds)->count();
                $check = count($categoryIds) == $categoriesCount;

                if (!$check) {
                    $fail(" A selected category no longer exists ");
                }

                return $check;
            }],
            'images.*' => ['image', 'max:5048']
        ];
    }
}
