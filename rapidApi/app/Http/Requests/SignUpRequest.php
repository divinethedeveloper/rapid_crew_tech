<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignUpRequest extends FormRequest
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
            'fullname' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone_number' => ['required'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }

    public function messages()
    {
        return [
            'fullname.required' => 'Your name is required',
            'phone_number.required' => 'Your phone number is required',
        ];
    }
}
