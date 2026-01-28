<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category' => 'required|string|max:100',
            'status' => 'required|in:active,inactive,discontinued',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The product name field is required.',
            'name.max' => 'The product name may not be greater than 255 characters.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'price.required' => 'The price field is required.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be at least 0.',
            'quantity.required' => 'The quantity field is required.',
            'quantity.integer' => 'The quantity must be an integer.',
            'quantity.min' => 'The quantity must be at least 0.',
            'category.required' => 'The category field is required.',
            'category.max' => 'The category may not be greater than 100 characters.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The status must be one of: active, inactive, discontinued.',
        ];
    }
}
