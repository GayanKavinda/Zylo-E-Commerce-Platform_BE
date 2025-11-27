<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin and seller users
        $admin = User::where('email', 'admin@example.com')->first();
        $seller = User::where('email', 'seller@example.com')->first();

        $products = [
            // Electronics
            [
                'name' => 'Wireless Bluetooth Headphones',
                'description' => 'Premium noise-canceling wireless headphones with 30-hour battery life',
                'price' => 149.99,
                'discount_price' => 119.99,
                'stock' => 50,
                'category' => 'Electronics',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500',
                ]),
                'sku' => 'ELECT-001',
                'is_active' => true,
                'owner_id' => $seller->id ?? $admin->id,
            ],
            [
                'name' => 'Smart Watch Series 5',
                'description' => 'Fitness tracker with heart rate monitor, GPS, and waterproof design',
                'price' => 299.99,
                'stock' => 30,
                'category' => 'Electronics',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500',
                ]),
                'sku' => 'ELECT-002',
                'is_active' => true,
                'owner_id' => $admin->id,
            ],
            [
                'name' => '4K Ultra HD Action Camera',
                'description' => 'Waterproof action camera with image stabilization and WiFi connectivity',
                'price' => 199.99,
                'discount_price' => 179.99,
                'stock' => 25,
                'category' => 'Electronics',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=500',
                ]),
                'sku' => 'ELECT-003',
                'is_active' => true,
                'owner_id' => $seller->id ?? $admin->id,
            ],

            // Clothing
            [
                'name' => 'Classic Cotton T-Shirt',
                'description' => 'Comfortable 100% cotton t-shirt available in multiple colors',
                'price' => 24.99,
                'stock' => 100,
                'category' => 'Clothing',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=500',
                ]),
                'sku' => 'CLOTH-001',
                'is_active' => true,
                'owner_id' => $admin->id,
            ],
            [
                'name' => 'Premium Denim Jeans',
                'description' => 'Stylish slim-fit jeans with stretch fabric for comfort',
                'price' => 79.99,
                'discount_price' => 59.99,
                'stock' => 60,
                'category' => 'Clothing',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1542272604-787c3835535d?w=500',
                ]),
                'sku' => 'CLOTH-002',
                'is_active' => true,
                'owner_id' => $seller->id ?? $admin->id,
            ],

            // Home & Garden
            [
                'name' => 'Ergonomic Office Chair',
                'description' => 'Adjustable lumbar support office chair with breathable mesh back',
                'price' => 249.99,
                'stock' => 20,
                'category' => 'Home & Garden',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1580480055273-228ff5388ef8?w=500',
                ]),
                'sku' => 'HOME-001',
                'is_active' => true,
                'owner_id' => $admin->id,
            ],
            [
                'name' => 'LED Desk Lamp',
                'description' => 'Modern LED desk lamp with adjustable brightness and USB charging port',
                'price' => 49.99,
                'discount_price' => 39.99,
                'stock' => 45,
                'category' => 'Home & Garden',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=500',
                ]),
                'sku' => 'HOME-002',
                'is_active' => true,
                'owner_id' => $seller->id ?? $admin->id,
            ],

            // Books
            [
                'name' => 'The Art of Programming',
                'description' => 'Comprehensive guide to modern software development practices',
                'price' => 44.99,
                'stock' => 80,
                'category' => 'Books',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=500',
                ]),
                'sku' => 'BOOK-001',
                'is_active' => true,
                'owner_id' => $admin->id,
            ],

            // Sports & Outdoors
            [
                'name' => 'Yoga Mat Premium',
                'description' => 'Non-slip yoga mat with extra cushioning, 6mm thick',
                'price' => 34.99,
                'stock' => 70,
                'category' => 'Sports & Outdoors',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=500',
                ]),
                'sku' => 'SPORT-001',
                'is_active' => true,
                'owner_id' => $seller->id ?? $admin->id,
            ],
            [
                'name' => 'Camping Tent 4-Person',
                'description' => 'Waterproof camping tent with easy setup, fits 4 people',
                'price' => 159.99,
                'discount_price' => 139.99,
                'stock' => 15,
                'category' => 'Sports & Outdoors',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?w=500',
                ]),
                'sku' => 'SPORT-002',
                'is_active' => true,
                'owner_id' => $admin->id,
            ],

            // Beauty & Personal Care
            [
                'name' => 'Organic Face Serum',
                'description' => 'Anti-aging face serum with natural ingredients',
                'price' => 39.99,
                'stock' => 90,
                'category' => 'Beauty & Personal Care',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=500',
                ]),
                'sku' => 'BEAUTY-001',
                'is_active' => true,
                'owner_id' => $seller->id ?? $admin->id,
            ],

            // Toys & Games
            [
                'name' => 'Educational Building Blocks Set',
                'description' => '500-piece building blocks set for kids ages 6+',
                'price' => 54.99,
                'discount_price' => 44.99,
                'stock' => 40,
                'category' => 'Toys & Games',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1587654780291-39c9404d746b?w=500',
                ]),
                'sku' => 'TOY-001',
                'is_active' => true,
                'owner_id' => $admin->id,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        $this->command->info('Sample products created successfully!');
    }
}
