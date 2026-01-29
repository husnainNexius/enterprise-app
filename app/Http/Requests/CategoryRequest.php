<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
        $categoryId = $this->route('id') ?? $this->route('category');

        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $categoryId,
            'description' => 'nullable|string|max:2000',
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $categoryId,
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The category name field is required.',
            'name.max' => 'The category name may not be greater than 255 characters.',
            'slug.required' => 'The slug field is required.',
            'slug.unique' => 'The slug must be unique.',
            'slug.max' => 'The slug may not be greater than 255 characters.',
            'description.max' => 'The description may not be greater than 2000 characters.',
            'parent_id.exists' => 'The selected parent category is invalid.',
            'parent_id.not_in' => 'A category cannot be its own parent.',
            'sort_order.required' => 'The sort order field is required.',
            'sort_order.integer' => 'The sort order must be an integer.',
            'sort_order.min' => 'The sort order must be at least 0.',
            'is_active.required' => 'The status field is required.',
            'is_active.boolean' => 'The status must be true or false.',
            'meta_title.max' => 'The meta title may not be greater than 255 characters.',
            'meta_description.max' => 'The meta description may not be greater than 500 characters.',
        ];
    }
}
