<?php

namespace App\Http\Controllers;

use App\Http\Services\OrderService;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderItemResource;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'date_from' => ['nullable', 'date'],
                'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            ]);

        

            $query = Order::with(['customer']); // Load customer relationship for display

            // Apply filters
            if ($request->has('status') && $request->input('status')) {
                $query->where('status', $request->input('status'));
            }
            
            // Apply payment_status filter
            if ($request->has('payment_status') && $request->input('payment_status')) {
                $query->where('payment_status', $request->input('payment_status'));
            }
            
            // Apply customer_id filter
            if ($request->has('customer_id')) {
                $query->where('customer_id', $request->input('customer_id'));
            }
            
            // Apply date filters
            if ($request->filled('date_from')) {
                $dateFrom = $request->input('date_from');
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            if ($request->filled('date_to')) {
                $dateTo = $request->input('date_to');
                $query->whereDate('created_at', '<=', $dateTo);
            }
            
            // Apply search filter (order number or customer name)
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', function ($customerQuery) use ($search) {
                          $customerQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }


            // Debug: Log the SQL query

            $orders = $query->orderBy('created_at', 'desc')
                          ->paginate($request->input('per_page', 15));


            $response = OrderResource::collection($orders)->response();

            return $response;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created order.
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->validated());
            return response()->json(new OrderResource($order), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $order = Order::with(['customer', 'items.product', 'statusHistory.changedByUser', 'payments'])
                        ->findOrFail($id);
            return response()->json(new OrderResource($order));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified order.
     */
    public function update(UpdateOrderRequest $request, string $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            
            if (!$order->canBeUpdated()) {
                return response()->json(['error' => 'Order cannot be updated in current status'], 422);
            }

            // Update basic order fields
            $validated = $request->validated();
            $order->update([
                'notes' => $validated['notes'] ?? $order->notes,
                'shipping_address' => $validated['shipping_address'] ?? $order->shipping_address,
            ]);

            // Update order items if provided
            if (isset($validated['items']) && is_array($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    $orderItem = $order->items()->findOrFail($itemData['id']);
                    
                    // Update quantity and recalculate total
                    $orderItem->update([
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $itemData['quantity'] * $itemData['unit_price']
                    ]);
                }
                
                // Recalculate order total
                $totalAmount = $order->items()->sum('total_price');
                $order->update(['total_amount' => $totalAmount]);
            }
            
            return response()->json(new OrderResource($order->fresh()));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified order (cancel).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $reason = request()->input('reason', 'Order cancelled by user');
            $order = $this->orderService->cancelOrder($id, $reason);
            return response()->json(new OrderResource($order));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get order items.
     */
    public function items(string $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            $items = $order->items()->with('product')->get();
            return OrderItemResource::collection($items)->response();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update order status.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, string $id): JsonResponse
    {
        try {
            $order = $this->orderService->updateOrderStatus(
                $id,
                $request->input('status'),
                $request->input('notes')
            );
            return response()->json(new OrderResource($order));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get order status history.
     */
    public function history(string $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            $history = $order->statusHistory()->with('changedByUser')->get();
            return response()->json($history);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get order statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to', 'status']);
            $statistics = $this->orderService->getOrderStatistics($filters);
            return response()->json($statistics);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Calculate order totals.
     */
    public function calculateTotals(Request $request): JsonResponse
    {
        try {
            $items = $request->input('items', []);
            $totals = $this->orderService->calculateOrderTotals($items);
            return response()->json($totals);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Apply discount to order.
     */
    public function applyDiscount(Request $request, string $id): JsonResponse
    {
        try {
            $discountAmount = $request->input('discount_amount');
            $order = $this->orderService->applyDiscount($id, $discountAmount);
            return response()->json(new OrderResource($order));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Process order.
     */
    public function process(string $id): JsonResponse
    {
        try {
            $this->orderService->processOrder($id);
            $order = Order::findOrFail($id);
            return response()->json(new OrderResource($order));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
