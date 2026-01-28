<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
            'customer' => $this->when($this->relationLoaded('customer'), function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                    'email' => $this->customer->email,
                ];
            }),
            'status' => $this->status,
            'total_amount' => (float) $this->total_amount,
            'tax_amount' => (float) $this->tax_amount,
            'shipping_amount' => (float) $this->shipping_amount,
            'discount_amount' => (float) $this->discount_amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'notes' => $this->notes,
            'order_date' => $this->order_date,
            'shipped_date' => $this->shipped_date,
            'delivered_date' => $this->delivered_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Computed fields
            'total_with_tax' => (float) $this->getTotalWithTax(),
            'grand_total' => (float) $this->getGrandTotal(),
            'can_be_updated' => $this->canBeUpdated(),
            'can_be_cancelled' => $this->canBeCancelled(),
            'is_paid' => $this->isPaid(),
            
            // Relationships
            'items' => $this->when($this->relationLoaded('items'), function () {
                return OrderItemResource::collection($this->items);
            }),
            'status_history' => $this->when($this->relationLoaded('statusHistory'), function () {
                return $this->statusHistory->map(function ($history) {
                    return [
                        'id' => $history->id,
                        'from_status' => $history->from_status,
                        'to_status' => $history->to_status,
                        'notes' => $history->notes,
                        'changed_by' => $history->when($history->relationLoaded('changedByUser'), function () use ($history) {
                            return [
                                'id' => $history->changedByUser->id,
                                'name' => $history->changedByUser->name,
                            ];
                        }),
                        'created_at' => $history->created_at,
                    ];
                });
            }),
            'payments' => $this->when($this->relationLoaded('payments'), function () {
                return $this->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'payment_method' => $payment->payment_method,
                        'amount' => (float) $payment->amount,
                        'status' => $payment->status,
                        'transaction_id' => $payment->transaction_id,
                        'created_at' => $payment->created_at,
                    ];
                });
            }),
        ];
    }
}
