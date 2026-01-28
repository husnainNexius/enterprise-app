<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Electronics', 'Clothing', 'Food', 'Books', 'Toys', 'Home', 'Sports', 'Other'];
        $statuses = ['active', 'inactive', 'discontinued'];
        
        $products = [
            ['Laptop Pro 15', 'High-performance laptop with 16GB RAM and 512GB SSD', 1299.99, 25, 'Electronics', 'active'],
            ['Wireless Mouse', 'Ergonomic wireless mouse with long battery life', 29.99, 150, 'Electronics', 'active'],
            ['Cotton T-Shirt', 'Comfortable 100% cotton t-shirt', 19.99, 200, 'Clothing', 'active'],
            ['Running Shoes', 'Professional running shoes with advanced cushioning', 89.99, 75, 'Sports', 'active'],
            ['Coffee Beans', 'Premium arabica coffee beans 1kg', 24.99, 100, 'Food', 'active'],
            ['JavaScript Guide', 'Complete JavaScript programming guide', 39.99, 50, 'Books', 'active'],
            ['Building Blocks Set', 'Educational building blocks for kids', 34.99, 60, 'Toys', 'active'],
            ['Kitchen Blender', 'High-power blender for smoothies and soups', 79.99, 30, 'Home', 'active'],
            ['Yoga Mat', 'Non-slip exercise yoga mat', 24.99, 120, 'Sports', 'active'],
            ['Smartphone Case', 'Protective case for latest smartphones', 15.99, 300, 'Electronics', 'active'],
        ];

        // Insert the first 10 predefined products
        foreach ($products as $product) {
            Product::create([
                'name' => $product[0],
                'description' => $product[1],
                'price' => $product[2],
                'quantity' => $product[3],
                'category' => $product[4],
                'status' => $product[5],
            ]);
        }

        // Generate 90 more random products
        for ($i = 11; $i <= 100; $i++) {
            $category = $categories[array_rand($categories)];
            $status = $statuses[array_rand($statuses)];
            $price = rand(5, 500) + (rand(0, 99) / 100);
            $quantity = rand(0, 200);
            
            Product::create([
                'name' => "Product $i - " . $this->generateProductName($category),
                'description' => "High-quality $category product with excellent features and durability. Perfect for everyday use.",
                'price' => $price,
                'quantity' => $quantity,
                'category' => $category,
                'status' => $status,
            ]);
        }
    }

    private function generateProductName($category)
    {
        $names = [
            'Electronics' => ['Gadget Pro', 'Tech Master', 'Digital Elite', 'Smart Device', 'Power Tool'],
            'Clothing' => ['Fashion Wear', 'Comfort Fit', 'Style Plus', 'Classic Design', 'Modern Look'],
            'Food' => ['Organic Choice', 'Fresh Taste', 'Premium Quality', 'Natural Flavor', 'Healthy Option'],
            'Books' => ['Bestseller', 'Classic Edition', 'Learning Guide', 'Reference Book', 'Story Collection'],
            'Toys' => ['Fun Play', 'Educational Set', 'Creative Kit', 'Adventure Game', 'Puzzle Master'],
            'Home' => ['Living Essential', 'Comfort Plus', 'Modern Design', 'Practical Tool', 'Decor Item'],
            'Sports' => ['Performance Gear', 'Training Equipment', 'Professional Grade', 'Fitness Tool', 'Sport Accessory'],
            'Other' => ['Multi-Purpose', 'Universal Tool', 'All-in-One', 'Versatile Item', 'General Use']
        ];

        $categoryNames = $names[$category] ?? $names['Other'];
        return $categoryNames[array_rand($categoryNames)];
    }
}
