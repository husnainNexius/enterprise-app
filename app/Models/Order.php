<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'status',
        'total_amount',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'payment_method',
        'payment_status',
        'shipping_address',
        'billing_address',
        'notes',
        'order_date',
        'shipped_date',
        'delivered_date',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'order_date' => 'datetime',
            'shipped_date' => 'datetime',
            'delivered_date' => 'datetime',
        ];
    }

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    // Methods
    public function generateOrderNumber(): string
    {
        return 'ORD-' . date('Y') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function canBeUpdated(): bool
    {
        return true; // Allow editing for all orders
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'processing']);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getTotalWithTax(): float
    {
        return $this->total_amount + $this->tax_amount;
    }

    public function getGrandTotal(): float
    {
        return $this->total_amount + $this->tax_amount + $this->shipping_amount - $this->discount_amount;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                // Get the next order number safely
                $lastOrder = static::orderBy('id', 'desc')->first();
                $nextId = $lastOrder ? $lastOrder->id + 1 : 1;
                $order->order_number = 'ORD-' . date('Y') . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
            }
            if (empty($order->order_date)) {
                $order->order_date = now();
            }
        });
    }
}
