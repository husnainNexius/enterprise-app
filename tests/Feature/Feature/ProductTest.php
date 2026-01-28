<?php

namespace Tests\Feature\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Models\User;

class ProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user for authentication
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_list_products()
    {
        // Create test products
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'name',
                             'description',
                             'price',
                             'quantity',
                             'category',
                             'status',
                             'created_at',
                             'updated_at'
                         ]
                     ]
                 ]);
    }

    /** @test */
    public function it_can_create_a_product()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'quantity' => 10,
            'category' => 'Electronics',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'id',
                     'name',
                     'description',
                     'price',
                     'quantity',
                     'category',
                     'status',
                     'created_at',
                     'updated_at'
                 ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'quantity' => 10,
            'category' => 'Electronics',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_product()
    {
        $response = $this->postJson('/api/products', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'price', 'quantity', 'category', 'status']);
    }

    /** @test */
    public function it_validates_price_is_numeric_when_creating_product()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 'invalid_price',
            'quantity' => 10,
            'category' => 'Electronics',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function it_validates_quantity_is_integer_when_creating_product()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'quantity' => 'invalid_quantity',
            'category' => 'Electronics',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function it_validates_status_is_valid_when_creating_product()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'quantity' => 10,
            'category' => 'Electronics',
            'status' => 'invalid_status'
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_can_show_a_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'name',
                     'description',
                     'price',
                     'quantity',
                     'category',
                     'status',
                     'created_at',
                     'updated_at'
                 ])
                 ->assertJson([
                     'id' => $product->id,
                     'name' => $product->name,
                     'price' => (float) $product->price,
                     'quantity' => $product->quantity,
                     'category' => $product->category,
                     'status' => $product->status
                 ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_product()
    {
        $response = $this->getJson('/api/products/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_a_product()
    {
        $product = Product::factory()->create();

        $updateData = [
            'name' => 'Updated Product',
            'description' => 'Updated Description',
            'price' => 149.99,
            'quantity' => 20,
            'category' => 'Updated Category',
            'status' => 'inactive'
        ];

        $response = $this->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $product->id,
                     'name' => 'Updated Product',
                     'description' => 'Updated Description',
                     'price' => 149.99,
                     'quantity' => 20,
                     'category' => 'Updated Category',
                     'status' => 'inactive'
                 ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 149.99,
            'quantity' => 20,
            'category' => 'Updated Category',
            'status' => 'inactive'
        ]);
    }

    /** @test */
    public function it_can_delete_a_product()
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Product deleted successfully']);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_product()
    {
        $response = $this->deleteJson('/api/products/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_filter_products_by_status()
    {
        Product::factory()->create(['status' => 'active']);
        Product::factory()->create(['status' => 'inactive']);
        Product::factory()->create(['status' => 'discontinued']);

        $response = $this->getJson('/api/products?status[]=active&status[]=inactive');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');

        $statuses = collect($response->json('data'))->pluck('status')->unique()->toArray();
        $this->assertEqualsCanonicalizing(['active', 'inactive'], $statuses);
    }

    /** @test */
    public function it_can_filter_products_by_category()
    {
        Product::factory()->create(['category' => 'Electronics']);
        Product::factory()->create(['category' => 'Clothing']);
        Product::factory()->create(['category' => 'Food']);

        $response = $this->getJson('/api/products?category=Electronics');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');

        $categories = collect($response->json('data'))->pluck('category')->unique()->toArray();
        $this->assertEqualsCanonicalizing(['Electronics'], $categories);
    }

    /** @test */
    public function it_can_search_products_by_name()
    {
        Product::factory()->create(['name' => 'Laptop Pro']);
        Product::factory()->create(['name' => 'Smartphone X']);
        Product::factory()->create(['name' => 'Tablet Plus']);

        $response = $this->getJson('/api/products?search=Laptop');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');

        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertEqualsCanonicalizing(['Laptop Pro'], $names);
    }

    /** @test */
    public function it_can_search_products_by_description()
    {
        Product::factory()->create(['description' => 'High-performance laptop with 16GB RAM']);
        Product::factory()->create(['description' => 'Water-resistant smartphone with great camera']);
        Product::factory()->create(['description' => 'Lightweight tablet for reading']);

        $response = $this->getJson('/api/products?search=performance');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');

        $descriptions = collect($response->json('data'))->pluck('description')->toArray();
        $this->assertArrayHasKey(0, array_filter($descriptions, fn($desc) => str_contains($desc, 'performance')));
    }

    /** @test */
    public function it_paginates_products_correctly()
    {
        Product::factory()->count(25)->create();

        $response = $this->getJson('/api/products?per_page=10&page=1');

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
    public function it_sorts_products_by_name_ascending_by_default()
    {
        Product::factory()->create(['name' => 'B Product']);
        Product::factory()->create(['name' => 'A Product']);
        Product::factory()->create(['name' => 'C Product']);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        
        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertEqualsCanonicalizing(['A Product', 'B Product', 'C Product'], $names);
    }

    /** @test */
    public function it_handles_empty_database_gracefully()
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonCount(0, 'data');
    }

    /** @test */
    public function it_validates_max_length_for_name_field()
    {
        $productData = [
            'name' => str_repeat('a', 300), // Exceeds 255 character limit
            'description' => 'Test Description',
            'price' => 99.99,
            'quantity' => 10,
            'category' => 'Electronics',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_validates_min_value_for_price_field()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => -10, // Negative price
            'quantity' => 10,
            'category' => 'Electronics',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function it_validates_min_value_for_quantity_field()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'quantity' => -5, // Negative quantity
            'category' => 'Electronics',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['quantity']);
    }
}
