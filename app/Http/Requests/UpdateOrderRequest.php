<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
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
            'notes' => 'nullable|string|max:1000',
            'shipping_address' => 'sometimes|array',
            'shipping_address.name' => 'required_with:shipping_address|string|max:255',
            'shipping_address.email' => 'required_with:shipping_address|email|max:255',
            'shipping_address.phone' => 'required_with:shipping_address|string|max:20',
            'shipping_address.address' => 'required_with:shipping_address|string|max:500',
            'shipping_address.city' => 'required_with:shipping_address|string|max:100',
            'shipping_address.state' => 'required_with:shipping_address|string|max:100',
            'shipping_address.postal_code' => 'required_with:shipping_address|string|max:20',
            'shipping_address.country' => 'required_with:shipping_address|string|max:100',
            'items' => 'sometimes|array',
            'items.*.id' => 'required|integer|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }
}
