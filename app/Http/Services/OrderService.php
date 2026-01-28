<?php

namespace App\Http\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\OrderPayment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Create a new order with items
     */
    public function createOrder(array $orderData): Order
    {
        return DB::transaction(function () use ($orderData) {
            // Calculate totals from items
            $totals = $this->calculateOrderTotals($orderData['items']);

            // Create order
            $order = Order::create([
                'customer_id' => $orderData['customer_id'],
                'status' => 'pending',
                'total_amount' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'shipping_amount' => $totals['shipping_amount'],
                'discount_amount' => 0,
                'payment_method' => $orderData['payment_method'],
                'shipping_address' => $orderData['shipping_address'],
                'billing_address' => $orderData['billing_address'] ?? $orderData['shipping_address'],
                'notes' => $orderData['notes'] ?? null,
            ]);

            // Create order items
            foreach ($orderData['items'] as $item) {
                $this->createOrderItem($order->id, $item);
            }

            // Record initial status (temporarily disabled due to status history issues)
            // $changedBy = auth()->check() ? auth()->id() : null;
            // $this->recordStatusChange($order->id, null, 'pending', $changedBy);


            return $order->load(['items', 'customer']);
        });
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(int $orderId, string $status, ?string $notes = null): Order
    {
        $order = Order::findOrFail($orderId);
        $oldStatus = $order->status;

        if (!$this->isValidStatusTransition($oldStatus, $status)) {
            throw new \InvalidArgumentException("Invalid status transition from {$oldStatus} to {$status}");
        }

        return DB::transaction(function () use ($order, $status, $oldStatus, $notes) {
            $order->update(['status' => $status]);

            // Update date fields based on status
            if ($status === 'shipped') {
                $order->update(['shipped_date' => now()]);
            } elseif ($status === 'delivered') {
                $order->update(['delivered_date' => now()]);
            }

            // Record status change
            $changedBy = auth()->check() ? auth()->id() : 1; // Default to user ID 1 if not authenticated
            $this->recordStatusChange($order->id, $oldStatus, $status, $changedBy, $notes);

           
            return $order->fresh();
        });
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(int $orderId, string $reason): Order
    {
        $order = Order::findOrFail($orderId);

        if (!$order->canBeCancelled()) {
            throw new \InvalidArgumentException("Order cannot be cancelled in current status: {$order->status}");
        }

        return $this->updateOrderStatus($orderId, 'cancelled', $reason);
    }

    /**
     * Calculate order totals
     */
    public function calculateOrderTotals(array $items): array
    {
        $subtotal = 0;
        $taxAmount = 0;
        $shippingAmount = 0;

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $itemTotal = $item['quantity'] * $product->price;
            $subtotal += $itemTotal;
        }

        // Calculate tax (10% for example)
        $taxAmount = $subtotal * 0.1;
        
        // Calculate shipping (flat rate for example)
        $shippingAmount = $subtotal > 100 ? 0 : 10;

        $totalAmount = $subtotal + $taxAmount + $shippingAmount;

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Apply discount to order
     */
    public function applyDiscount(int $orderId, float $discountAmount): Order
    {
        $order = Order::findOrFail($orderId);

        if ($discountAmount > $order->total_amount) {
            throw new \InvalidArgumentException('Discount amount cannot exceed order total');
        }

        $order->update(['discount_amount' => $discountAmount]);

     

        return $order->fresh();
    }

    /**
     * Process order (move to processing status)
     */
    public function processOrder(int $orderId): bool
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== 'confirmed') {
            throw new \InvalidArgumentException("Order must be confirmed before processing");
        }

        // Check inventory
        foreach ($order->items as $item) {
            if ($item->product->quantity < $item->quantity) {
                throw new \InvalidArgumentException("Insufficient inventory for product: {$item->product->name}");
            }
        }

        // Update inventory
        foreach ($order->items as $item) {
            $item->product->decrement('quantity', $item->quantity);
        }

        $this->updateOrderStatus($orderId, 'processing');

        return true;
    }

    /**
     * Get order statistics
     */
    public function getOrderStatistics(array $filters = []): array
    {
        $query = Order::query();

        // Apply filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('order_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('order_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            $query->whereIn('status', $filters['status']);
        }

        $totalOrders = $query->count();
        $totalRevenue = $query->sum('total_amount');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $statusBreakdown = Order::selectRaw('status, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('status')
            ->get();

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'average_order_value' => $averageOrderValue,
            'status_breakdown' => $statusBreakdown,
        ];
    }

    /**
     * Create order item
     */
    private function createOrderItem(int $orderId, array $itemData): OrderItem
    {
        $product = Product::findOrFail($itemData['product_id']);

        return OrderItem::create([
            'order_id' => $orderId,
            'product_id' => $itemData['product_id'],
            'quantity' => $itemData['quantity'],
            'unit_price' => $product->price,
            'total_price' => $itemData['quantity'] * $product->price,
            'product_snapshot' => [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category' => $product->category,
            ],
        ]);
    }

    /**
     * Record status change in history
     */
    private function recordStatusChange(int $orderId, ?string $fromStatus, string $toStatus, ?int $changedBy, ?string $notes = null): OrderStatusHistory
    {
        return OrderStatusHistory::create([
            'order_id' => $orderId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => $changedBy ?? 1, // Default to user ID 1 if null
            'notes' => $notes,
        ]);
    }

    /**
     * Validate status transition
     */
    private function isValidStatusTransition(string $fromStatus, string $toStatus): bool
    {
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered'],
            'delivered' => ['refunded'],
            'cancelled' => [],
            'refunded' => [],
        ];

        return in_array($toStatus, $validTransitions[$fromStatus] ?? []);
    }
}
