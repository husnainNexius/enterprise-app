<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow authenticated users to create orders
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,paypal,stripe',
            'shipping_address' => 'required|array',
            'shipping_address.name' => 'required|string|max:255',
            'shipping_address.email' => 'required|email|max:255',
            'shipping_address.phone' => 'required|string|max:20',
            'shipping_address.address' => 'required|string|max:500',
            'shipping_address.city' => 'required|string|max:100',
            'shipping_address.state' => 'required|string|max:100',
            'shipping_address.postal_code' => 'required|string|max:20',
            'shipping_address.country' => 'required|string|max:100',
            'billing_address' => 'sometimes|array',
            'billing_address.name' => 'required_with:billing_address|string|max:255',
            'billing_address.email' => 'required_with:billing_address|email|max:255',
            'billing_address.phone' => 'required_with:billing_address|string|max:20',
            'billing_address.address' => 'required_with:billing_address|string|max:500',
            'billing_address.city' => 'required_with:billing_address|string|max:100',
            'billing_address.state' => 'required_with:billing_address|string|max:100',
            'billing_address.postal_code' => 'required_with:billing_address|string|max:20',
            'billing_address.country' => 'required_with:billing_address|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer is required',
            'customer_id.exists' => 'Selected customer does not exist',
            'items.required' => 'At least one item is required',
            'items.min' => 'At least one item is required',
            'items.*.product_id.required' => 'Product is required for each item',
            'items.*.product_id.exists' => 'Selected product does not exist',
            'items.*.quantity.required' => 'Quantity is required for each item',
            'items.*.quantity.min' => 'Quantity must be at least 1',
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method',
            'shipping_address.required' => 'Shipping address is required',
            'shipping_address.name.required' => 'Shipping name is required',
            'shipping_address.email.required' => 'Shipping email is required',
            'shipping_address.email.email' => 'Shipping email must be valid',
            'shipping_address.phone.required' => 'Shipping phone is required',
            'shipping_address.address.required' => 'Shipping address is required',
            'shipping_address.city.required' => 'Shipping city is required',
            'shipping_address.state.required' => 'Shipping state is required',
            'shipping_address.postal_code.required' => 'Shipping postal code is required',
            'shipping_address.country.required' => 'Shipping country is required',
        ];
    }
}
