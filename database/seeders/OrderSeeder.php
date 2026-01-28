<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();
        
        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->error('Please run UserSeeder and ProductSeeder first!');
            return;
        }

        $orderStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        $paymentMethods = ['cash', 'card', 'paypal', 'stripe'];
        $paymentStatuses = ['pending', 'paid', 'failed'];

        // Create 50 sample orders
        for ($i = 1; $i <= 50; $i++) {
            $customer = $users->random();
            $status = $orderStatuses[array_rand($orderStatuses)];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

            // Generate random shipping address
            $shippingAddress = [
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $this->generatePhoneNumber(),
                'address' => $this->generateAddress(),
                'city' => $this->generateCity(),
                'state' => $this->generateState(),
                'postal_code' => $this->generatePostalCode(),
                'country' => 'USA',
            ];

            // Calculate order totals
            $orderItems = [];
            $totalAmount = 0;
            $itemCount = rand(1, 5);

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(1, 5);
                $unitPrice = $product->price;
                $totalPrice = $unitPrice * $quantity;
                
                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'product_snapshot' => [
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => $product->price,
                        'category' => $product->category,
                    ],
                ];
                
                $totalAmount += $totalPrice;
            }

            $taxAmount = $totalAmount * 0.1; // 10% tax
            $shippingAmount = $totalAmount > 100 ? 0 : 10; // Free shipping over $100
            $discountAmount = rand(0, 10) > 7 ? rand(5, 50) : 0; // 30% chance of discount

            // Generate order number
            $lastOrder = Order::orderBy('id', 'desc')->first();
            $nextId = $lastOrder ? $lastOrder->id + 1 : 1;
            $orderNumber = 'ORD-' . date('Y') . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'status' => $status,
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => $discountAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'shipping_address' => $shippingAddress,
                'billing_address' => $shippingAddress,
                'notes' => $this->generateOrderNotes($status),
                'order_date' => now()->subDays(rand(0, 30)),
                'shipped_date' => in_array($status, ['shipped', 'delivered']) ? now()->subDays(rand(1, 15)) : null,
                'delivered_date' => $status === 'delivered' ? now()->subDays(rand(1, 7)) : null,
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                    'product_snapshot' => $item['product_snapshot'],
                ]);
            }
        }

        $this->command->info('Orders seeded successfully!');
    }

    private function generatePhoneNumber()
    {
        return sprintf('(%d) %d-%d', rand(200, 999), rand(200, 999), rand(1000, 9999));
    }

    private function generateAddress()
    {
        $streetNumbers = [123, 456, 789, 321, 654, 987, 111, 222, 333, 444];
        $streetNames = ['Main St', 'Oak Ave', 'Pine Rd', 'Elm Dr', 'Maple Ln', 'Cedar Ct', 'Birch Way', 'Park Blvd', 'Washington Ave', 'Lincoln St'];
        return $streetNumbers[array_rand($streetNumbers)] . ' ' . $streetNames[array_rand($streetNames)];
    }

    private function generateCity()
    {
        $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose'];
        return $cities[array_rand($cities)];
    }

    private function generateState()
    {
        $states = ['NY', 'CA', 'IL', 'TX', 'AZ', 'PA', 'FL', 'OH', 'GA', 'NC'];
        return $states[array_rand($states)];
    }

    private function generatePostalCode()
    {
        return sprintf('%05d', rand(10000, 99999));
    }

    private function generateOrderNotes($status)
    {
        $notes = [
            'pending' => ['Customer requested express shipping', 'Please verify payment method', 'Gift order - wrap separately'],
            'confirmed' => ['Order confirmed, ready for processing', 'Customer requested delivery confirmation', 'Priority order'],
            'processing' => ['Items being prepared for shipment', 'Quality check in progress', 'Custom packaging requested'],
            'shipped' => ['Shipped via UPS', 'Tracking number: ' . strtoupper(substr(md5(time()), 0, 10)), 'Delivery expected in 3-5 days'],
            'delivered' => ['Delivered successfully', 'Customer satisfied with product', 'Left at front door'],
            'cancelled' => ['Customer requested cancellation', 'Out of stock items', 'Payment processing failed'],
        ];

        if (isset($notes[$status])) {
            return $notes[$status][array_rand($notes[$status])];
        }

        return 'Standard order processing';
    }
}
