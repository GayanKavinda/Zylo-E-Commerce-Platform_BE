<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

class TestOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a customer and seller
        $customer = User::where('email', 'customer@example.com')->first();
        $seller = User::where('email', 'seller@example.com')->first();
        
        if (!$customer || !$seller) {
            echo "Missing customer or seller. Please run UserSeeder first.\n";
            echo "Customer found: " . ($customer ? "Yes" : "No") . "\n";
            echo "Seller found: " . ($seller ? "Yes" : "No") . "\n";
            return;
        }

        // Get seller's products
        $products = Product::where('owner_id', $seller->id)->take(3)->get();
        
        if ($products->isEmpty()) {
            echo "No products found for seller. Creating test products...\n";
            
            $products = collect([
                Product::create([
                    'name' => 'Test Product 1',
                    'description' => 'Test product description',
                    'price' => 99.99,
                    'stock' => 100,
                    'category' => 'Electronics',
                    'images' => json_encode(['https://via.placeholder.com/300']),
                    'sku' => 'TEST-001',
                    'is_active' => true,
                    'owner_id' => $seller->id,
                ]),
                Product::create([
                    'name' => 'Test Product 2',
                    'description' => 'Test product description 2',
                    'price' => 149.99,
                    'stock' => 50,
                    'category' => 'Electronics',
                    'images' => json_encode(['https://via.placeholder.com/300']),
                    'sku' => 'TEST-002',
                    'is_active' => true,
                    'owner_id' => $seller->id,
                ])
            ]);
        }

        // Create test orders
        $statuses = ['pending', 'processing', 'shipped'];
        
        foreach ($statuses as $index => $status) {
            $order = Order::create([
                'user_id' => $customer->id,
                'order_number' => 'ORD-TEST-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'total_amount' => 0,
                'subtotal' => 0,
                'tax' => 0,
                'shipping_fee' => 10.00,
                'status' => $status,
                'payment_status' => 'paid',
                'payment_method' => 'stripe',
                'shipping_address' => json_encode([
                    'address' => '123 Test Street',
                    'city' => 'Test City',
                    'state' => 'TS',
                    'postal_code' => '12345',
                    'country' => 'USA'
                ]),
            ]);

            $totalAmount = 0;
            
            // Add 1-2 items to each order
            $itemCount = rand(1, min(2, $products->count()));
            $selectedProducts = $products->random($itemCount);
            
            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $price = $product->price;
                $subtotal = $price * $quantity;
                $totalAmount += $subtotal;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'seller_id' => $seller->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'fulfillment_status' => $status === 'pending' ? 'pending' : ($status === 'processing' ? 'processing' : 'shipped'),
                ]);
            }
            
            // Update order total
            $order->update([
                'subtotal' => $totalAmount,
                'total_amount' => $totalAmount + 10.00, // Including shipping
            ]);
            
            echo "Created order {$order->order_number} with status {$status}\n";
        }
        
        echo "Test orders created successfully!\n";
    }
}
