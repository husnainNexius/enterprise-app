<?php

namespace Tests\Feature\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Http\Services\OrderService;

class OrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user for testing
        // $this->user = User::factory()->create();
        // $this->orderService = new OrderService();
    }

    /** @test */
    public function it_can_list_orders()
    {
        // Create test orders
        Order::factory()->count(5)->create();

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
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
                             'created_at',
                             'updated_at'
                         ]
                     ]
                 ]);
    }

    /** @test */
    public function it_can_create_an_order()
    {
        $customer = User::factory()->create();
        $product = \App\Models\Product::factory()->create();

        $orderData = [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2
                ]
            ],
            'payment_method' => 'card',
            'shipping_address' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '1234567890',
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'USA'
            ],
            'notes' => 'Test order notes'
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'id',
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
                     'created_at',
                     'updated_at'
                 ]);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'status' => 'pending',
            'payment_method' => 'card'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_order()
    {
        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['customer_id', 'items', 'payment_method', 'shipping_address']);
    }

    /** @test */
    public function it_validates_customer_exists_when_creating_order()
    {
        $product = \App\Models\Product::factory()->create();

        $orderData = [
            'customer_id' => 999, // Non-existent customer
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1
                ]
            ],
            'payment_method' => 'card',
            'shipping_address' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '1234567890',
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'USA'
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['customer_id']);
    }

    /** @test */
    public function it_validates_items_when_creating_order()
    {
        $customer = User::factory()->create();

        $orderData = [
            'customer_id' => $customer->id,
            'items' => [], // Empty items
            'payment_method' => 'card',
            'shipping_address' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '1234567890',
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'USA'
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['items']);
    }

    /** @test */
    public function it_validates_shipping_address_when_creating_order()
    {
        $customer = User::factory()->create();
        $product = \App\Models\Product::factory()->create();

        $orderData = [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1
                ]
            ],
            'payment_method' => 'card',
            'shipping_address' => [] // Empty address
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['shipping_address']);
    }

    /** @test */
    public function it_can_show_an_order()
    {
        $order = Order::factory()->create();

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
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
                     'created_at',
                     'updated_at'
                 ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_order()
    {
        $response = $this->getJson('/api/orders/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_an_order()
    {
        $order = Order::factory()->create();

        $updateData = [
            'notes' => 'Updated order notes'
        ];

        $response = $this->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $order->id,
                     'notes' => 'Updated order notes'
                 ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'notes' => 'Updated order notes'
        ]);
    }

    /** @test */
    public function it_can_cancel_an_order()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->deleteJson("/api/orders/{$order->id}", ['reason' => 'Customer request']);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'cancelled'
                 ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled'
        ]);
    }

    /** @test */
    public function it_cannot_cancel_completed_order()
    {
        $order = Order::factory()->create(['status' => 'delivered']);

        $response = $this->deleteJson("/api/orders/{$order->id}", ['reason' => 'Customer request']);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_update_order_status()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $statusData = [
            'status' => 'confirmed',
            'notes' => 'Order confirmed by admin'
        ];

        $response = $this->putJson("/api/orders/{$order->id}/status", $statusData);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'confirmed'
                 ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed'
        ]);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'confirmed',
            'notes' => 'Order confirmed by admin'
        ]);
    }

    /** @it */
    public function it_validates_status_transition_when_updating_order_status()
    {
        $order = Order::factory()->create(['status' => 'delivered']);

        $statusData = [
            'status' => 'pending', // Invalid transition
            'notes' => 'Invalid transition'
        ];

        $response = $this->putJson("/api/orders/{$order->id}/status", $statusData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_get_order_items()
    {
        $order = Order::factory()->create();
        $product = \App\Models\Product::factory()->create();
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price,
            'total_price' => $product->price * 2
        ]);

        $response = $this->getJson("/api/orders/{$order->id}/items");

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'order_id',
                             'product_id',
                             'quantity',
                             'unit_price',
                             'total_price',
                             'product_snapshot',
                             'created_at',
                             'updated_at'
                         ]
                     ]
                 ]);
    }

    /** @test */
    public function it_can_get_order_status_history()
    {
        $order = Order::factory()->create();
        
        // Create some status history
        \App\Models\OrderStatusHistory::factory()->create([
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'confirmed',
            'changed_by' => $this->user->id,
            'notes' => 'Status updated'
        ]);

        $response = $this->getJson("/api/orders/{$order->id}/history");

        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    /** @test */
    public function it_can_calculate_order_totals()
    {
        $product1 = \App\Models\Product::factory()->create(['price' => 50]);
        $product2 = \App\Models\Product::factory()->create(['price' => 75]);

        $items = [
            [
                'product_id' => $product1->id,
                'quantity' => 2
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 1
            ]
        ];

        $response = $this->postJson('/api/orders/calculate-totals', ['items' => $items]);

        $response->assertStatus(200)
                 ->assertJson([
                     'subtotal' => 175.0,
                     'tax_amount' => 17.5,
                     'shipping_amount' => 10.0,
                     'total_amount' => 202.5
                 ]);
    }

    /** @test */
    public function it_can_get_order_statistics()
    {
        Order::factory()->count(5)->create(['status' => 'pending']);
        Order::factory()->count(3)->create(['status' => 'confirmed']);
        Order::factory()->count(2)->create(['status' => 'delivered']);

        $response = $this->getJson('/api/orders/statistics');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'total_orders',
                     'total_revenue',
                     'average_order_value',
                     'status_breakdown'
                 ]);

        $this->assertEquals(10, $response->json('total_orders'));
        $this->assertArrayHasKey('status_breakdown', $response->json());
    }

    /** @test */
    public function it_can_filter_orders_by_status()
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'confirmed']);
        Order::factory()->create(['status' => 'delivered']);

        $response = $this->getJson('/api/orders?status[]=pending&status[]=confirmed');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');

        $statuses = collect($response->json('data'))->pluck('status')->unique()->toArray();
        $this->assertEqualsCanonicalizing(['pending', 'confirmed'], $statuses);
    }

    /** @test */
    public function it_can_filter_orders_by_customer()
    {
        $customer1 = User::factory()->create();
        $customer2 = User::factory()->create();
        
        Order::factory()->create(['customer_id' => $customer1->id]);
        Order::factory()->create(['customer_id' => $customer2->id]);
        Order::factory()->create(['customer_id' => $customer1->id]);

        $response = $this->getJson("/api/orders?customer_id={$customer1->id}");

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');

        $customerIds = collect($response->json('data'))->pluck('customer_id')->unique()->toArray();
        $this->assertEqualsCanonicalizing([$customer1->id, $customer1->id], $customerIds);
    }

    /** @test */
    public function it_can_search_orders_by_order_number()
    {
        $order1 = Order::factory()->create(['order_number' => 'ORD-2026-000001']);
        $order2 = Order::factory()->create(['order_number' => 'ORD-2026-000002']);
        Order::factory()->create(['order_number' => 'ORD-2026-000003']);

        $response = $this->getJson('/api/orders?search=ORD-2026-000002');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');

        $orderNumbers = collect($response->json('data'))->pluck('order_number')->toArray();
        $this->assertEqualsCanonicalizing(['ORD-2026-000002'], $orderNumbers);
    }

    /** @test */
    public function it_can_search_orders_by_customer_name()
    {
        $customer1 = User::factory()->create(['name' => 'John Doe']);
        $customer2 = User::factory()->create(['name' => 'Jane Smith']);
        
        Order::factory()->create(['customer_id' => $customer1->id]);
        Order::factory()->create(['customer_id' => $customer2->id]);

        $response = $this->getJson('/api/orders?search=Jane');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');

        $orderIds = collect($response->json('data'))->pluck('customer_id')->toArray();
        $this->assertEqualsCanonicalizing([$customer2->id], $orderIds);
    }

    /** @test */
    public function it_paginates_orders_correctly()
    {
        Order::factory()->count(25)->create();

        $response = $this->getJson('/api/orders?per_page=10&page=1');

        $response->assertStatus(200)
                 ->assertJsonCount(10, 'data')
                 ->assertJsonStructure([
                     'data',
                     'current_page',
                     'last_page',
                     'per_page',
                     'total',
                     'from',
                     'to'
                 ]);

        $this->assertEquals(1, $response->json('current_page'));
        $this->assertEquals(3, $response->json('last_page'));
        $this->assertEquals(10, $response->json('per_page'));
        $this->assertEquals(25, $response->json('total'));
    }

    /** @test */
    public function it_generates_unique_order_numbers()
    {
        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();
        $order3 = Order::factory()->create();

        $this->assertNotEquals($order1->order_number, $order2->order_number);
        $this->assertNotEquals($order2->order_number, $order3->order_number);
        $this->assertNotEquals($order1->order_number, $order3->order_number);
        
        // Check format
        $this->assertMatches('/^ORD-\d{4}-\d{6}$/', $order1->order_number);
    }

    /** @test */
    public function it_calculates_grand_total_correctly()
    {
        $order = Order::factory()->create([
            'total_amount' => 100.00,
            'tax_amount' => 10.00,
            'shipping_amount' => 5.00,
            'discount_amount' => 0
        ]);

        $this->assertEquals(115.00, $order->getGrandTotal());
    }

    /** @test */
    public function it_applies_discount_to_order()
    {
        $order = Order::factory()->create([
            'total_amount' => 100.00,
            'discount_amount' => 0
        ]);

        $response = $this->putJson("/api/orders/{$order->id}/apply-discount", ['discount_amount' => 10.00]);

        $response->assertStatus(200);

        $updatedOrder = Order::find($order->id);
        $this->assertEquals(10.00, $updatedOrder->discount_amount);
    }

    /** @test */
    public function it_cannot_apply_discount_greater_than_total()
    {
        $order = Order::factory()->create([
            'total_amount' => 100.00,
            'discount_amount' => 0
        ]);

        $response = $this->putJson("/api/orders/{$order->id}/apply-discount", ['discount_amount' => 150.00]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_process_order()
    {
        $order = Order::factory()->create(['status' => 'confirmed']);
        $product = \App\Models\Product::factory()->create(['quantity' => 10]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/process");

        $response->assertStatus(200);

        $updatedOrder = Order::find($order->id);
        $this->assertEquals('processing', $updatedOrder->status);
        
        // Check inventory was updated
        $updatedProduct = \App\Models\Product::find($product->id);
        $this->assertEquals(5, $updatedProduct->quantity);
    }

    /** @test */
    public function it_cannot_process_order_with_insufficient_inventory()
    {
        $order = Order::factory()->create(['status' => 'confirmed']);
        $product = \App\Models\Product::factory()->create(['quantity' => 2]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5 // More than available
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/process");

        $response->assertStatus(422);
    }

    /** @test */
    public function it_handles_empty_database_gracefully()
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
                 ->assertJsonCount(0, 'data');
    }

    /** @test */
    public function it_creates_order_status_history_automatically()
    {
        $customer = User::factory()->create();
        $product = \App\Models\Product::factory()->create();

        $orderData = [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2
                ]
            ],
            'payment_method' => 'card',
            'shipping_address' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '1234567890',
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'USA'
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $response->json('id'),
            'from_status' => null,
            'to_status' => 'pending',
            'changed_by' => null,
            'notes' => null
        ]);
    }

    /** @test */
    public function it_records_status_change_in_history()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $statusData = [
            'status' => 'confirmed',
            'notes' => 'Order confirmed by admin'
        ];

        $response = $this->putJson("/api/orders/{$order->id}/status", $statusData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'confirmed',
            'notes' => 'Order confirmed by admin'
        ]);
    }
}
