<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main categories
        $electronics = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic devices, gadgets, and accessories',
            'sort_order' => 1,
            'is_active' => true,
            'meta_title' => 'Electronics - Best Deals on Electronic Devices',
            'meta_description' => 'Find the best deals on smartphones, laptops, tablets, and more electronic devices.',
        ]);

        $clothing = Category::create([
            'name' => 'Clothing',
            'slug' => 'clothing',
            'description' => 'Fashion apparel for men, women, and children',
            'sort_order' => 2,
            'is_active' => true,
            'meta_title' => 'Clothing - Latest Fashion Trends',
            'meta_description' => 'Shop the latest fashion trends for men, women, and children.',
        ]);

        $home = Category::create([
            'name' => 'Home & Garden',
            'slug' => 'home-garden',
            'description' => 'Furniture, decor, and garden supplies',
            'sort_order' => 3,
            'is_active' => true,
            'meta_title' => 'Home & Garden - Furniture and Decor',
            'meta_description' => 'Transform your home with our collection of furniture and decor items.',
        ]);

        $sports = Category::create([
            'name' => 'Sports & Outdoors',
            'slug' => 'sports-outdoors',
            'description' => 'Sports equipment and outdoor gear',
            'sort_order' => 4,
            'is_active' => true,
            'meta_title' => 'Sports & Outdoors - Equipment and Gear',
            'meta_description' => 'Get equipped for your favorite sports and outdoor activities.',
        ]);

        $books = Category::create([
            'name' => 'Books & Media',
            'slug' => 'books-media',
            'description' => 'Books, movies, music, and digital media',
            'sort_order' => 5,
            'is_active' => true,
            'meta_title' => 'Books & Media - Entertainment Collection',
            'meta_description' => 'Discover books, movies, music, and digital entertainment.',
        ]);

        // Create subcategories for Electronics
        Category::create([
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'description' => 'Mobile phones and smartphones',
            'parent_id' => $electronics->id,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Laptops',
            'slug' => 'laptops',
            'description' => 'Notebook computers and laptops',
            'parent_id' => $electronics->id,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Tablets',
            'slug' => 'tablets',
            'description' => 'Tablet computers and accessories',
            'parent_id' => $electronics->id,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Audio & Video',
            'slug' => 'audio-video',
            'description' => 'Audio equipment and video devices',
            'parent_id' => $electronics->id,
            'sort_order' => 4,
            'is_active' => true,
        ]);

        // Create subcategories for Clothing
        Category::create([
            'name' => 'Men\'s Clothing',
            'slug' => 'mens-clothing',
            'description' => 'Clothing and apparel for men',
            'parent_id' => $clothing->id,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Women\'s Clothing',
            'slug' => 'womens-clothing',
            'description' => 'Clothing and apparel for women',
            'parent_id' => $clothing->id,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Kids\' Clothing',
            'slug' => 'kids-clothing',
            'description' => 'Clothing and apparel for children',
            'parent_id' => $clothing->id,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Shoes & Accessories',
            'slug' => 'shoes-accessories',
            'description' => 'Footwear and fashion accessories',
            'parent_id' => $clothing->id,
            'sort_order' => 4,
            'is_active' => true,
        ]);

        // Create subcategories for Home & Garden
        Category::create([
            'name' => 'Furniture',
            'slug' => 'furniture',
            'description' => 'Home and office furniture',
            'parent_id' => $home->id,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Kitchen & Dining',
            'slug' => 'kitchen-dining',
            'description' => 'Kitchen appliances and dining essentials',
            'parent_id' => $home->id,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Home Decor',
            'slug' => 'home-decor',
            'description' => 'Decorative items and home accessories',
            'parent_id' => $home->id,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Garden & Outdoor',
            'slug' => 'garden-outdoor',
            'description' => 'Gardening tools and outdoor living',
            'parent_id' => $home->id,
            'sort_order' => 4,
            'is_active' => true,
        ]);

        // Create subcategories for Sports & Outdoors
        Category::create([
            'name' => 'Fitness Equipment',
            'slug' => 'fitness-equipment',
            'description' => 'Exercise and fitness gear',
            'parent_id' => $sports->id,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Outdoor Recreation',
            'slug' => 'outdoor-recreation',
            'description' => 'Camping, hiking, and outdoor gear',
            'parent_id' => $sports->id,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Team Sports',
            'slug' => 'team-sports',
            'description' => 'Equipment for team sports',
            'parent_id' => $sports->id,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Create subcategories for Books & Media
        Category::create([
            'name' => 'Fiction Books',
            'slug' => 'fiction-books',
            'description' => 'Fiction literature and novels',
            'parent_id' => $books->id,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Non-Fiction Books',
            'slug' => 'non-fiction-books',
            'description' => 'Non-fiction and educational books',
            'parent_id' => $books->id,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Movies & TV',
            'slug' => 'movies-tv',
            'description' => 'Movies and TV shows on physical media',
            'parent_id' => $books->id,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Music',
            'slug' => 'music',
            'description' => 'Music albums and audio recordings',
            'parent_id' => $books->id,
            'sort_order' => 4,
            'is_active' => true,
        ]);

        $this->command->info('Categories seeded successfully!');
    }
}
