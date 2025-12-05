<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerController extends Controller
{
    /**
     * Get seller dashboard statistics
     */
    public function dashboard(Request $request)
    {
        $sellerId = $request->user()->id;

        // Products statistics
        $totalProducts = Product::where('owner_id', $sellerId)->count();
        $activeProducts = Product::where('owner_id', $sellerId)->where('is_active', true)->count();
        $outOfStock = Product::where('owner_id', $sellerId)->where('stock', 0)->count();

        // Sales statistics
        $totalSales = OrderItem::where('seller_id', $sellerId)
            ->whereHas('order', function($query) {
                $query->where('payment_status', 'paid');
            })
            ->sum('subtotal');

        $totalOrders = OrderItem::where('seller_id', $sellerId)
            ->distinct('order_id')
            ->count('order_id');

        $pendingOrders = OrderItem::where('seller_id', $sellerId)
            ->where('fulfillment_status', 'pending')
            ->count();

        $processingOrders = OrderItem::where('seller_id', $sellerId)
            ->where('fulfillment_status', 'processing')
            ->count();

        $shippedOrders = OrderItem::where('seller_id', $sellerId)
            ->where('fulfillment_status', 'shipped')
            ->count();

        // Recent sales (last 30 days)
        $recentSales = OrderItem::where('seller_id', $sellerId)
            ->whereHas('order', function($query) {
                $query->where('payment_status', 'paid')
                      ->where('created_at', '>=', now()->subDays(30));
            })
            ->selectRaw('DATE(order_items.created_at) as date, COUNT(*) as count, SUM(subtotal) as revenue')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        // Top selling products
        $topProducts = Product::where('owner_id', $sellerId)
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'products' => [
                'total' => $totalProducts,
                'active' => $activeProducts,
                'out_of_stock' => $outOfStock,
            ],
            'sales' => [
                'total_revenue' => $totalSales,
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'processing_orders' => $processingOrders,
                'shipped_orders' => $shippedOrders,
            ],
            'recent_sales' => $recentSales,
            'top_products' => $topProducts,
        ]);
    }

    /**
     * Get seller's products
     */
    public function products(Request $request)
    {
        $query = Product::where('owner_id', $request->user()->id)
            ->withCount(['reviews', 'orderItems']);

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($products);
    }

    /**
     * Get seller's orders to fulfill
     */
    public function orders(Request $request)
    {
        $sellerId = $request->user()->id;
        
        // Get unique orders that contain seller's products
        $query = Order::whereHas('items', function($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            })
            ->with(['user', 'items' => function($q) use ($sellerId) {
                $q->where('seller_id', $sellerId)->with('product');
            }]);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        // Transform the response to include only seller's items
        $orders->getCollection()->transform(function($order) use ($sellerId) {
            $sellerItems = $order->items->where('seller_id', $sellerId)->values();
            $sellerTotal = $sellerItems->sum('subtotal');
            
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => [
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ],
                'items' => $sellerItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product' => [
                            'name' => $item->product->name,
                            'images' => json_decode($item->product->images, true),
                        ],
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                        'fulfillment_status' => $item->fulfillment_status,
                    ];
                }),
                'total_amount' => $sellerTotal,
                'subtotal' => $sellerTotal,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'shipping_address' => $order->shipping_address,
                'created_at' => $order->created_at->toISOString(),
            ];
        });

        return response()->json($orders);
    }

    /**
     * Update order item fulfillment status
     */
    public function updateFulfillmentStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered',
        ]);

        // Get the order
        $order = Order::whereHas('items', function($q) use ($request) {
                $q->where('seller_id', $request->user()->id);
            })
            ->findOrFail($id);

        // Update all order items belonging to this seller
        $updatedCount = OrderItem::where('order_id', $order->id)
            ->where('seller_id', $request->user()->id)
            ->update(['fulfillment_status' => $request->status]);

        // Check if all items in the order are shipped/delivered
        $allItemsStatus = OrderItem::where('order_id', $order->id)
            ->pluck('fulfillment_status')
            ->unique();
        
        // Update main order status based on item statuses
        if ($allItemsStatus->count() === 1) {
            if ($allItemsStatus->first() === 'shipped') {
                $order->update(['status' => 'shipped']);
            } elseif ($allItemsStatus->first() === 'delivered') {
                $order->update(['status' => 'delivered']);
            } elseif ($allItemsStatus->first() === 'processing') {
                $order->update(['status' => 'processing']);
            }
        } elseif ($allItemsStatus->contains('shipped')) {
            $order->update(['status' => 'processing']);
        }

        return response()->json([
            'message' => 'Order status updated successfully',
            'updated_items' => $updatedCount,
        ]);
    }

    /**
     * Get seller analytics
     */
    public function analytics(Request $request)
    {
        $sellerId = $request->user()->id;
        $period = $request->get('period', 30); // days

        // Revenue over time
        $revenue = OrderItem::where('seller_id', $sellerId)
            ->whereHas('order', function($query) use ($period) {
                $query->where('payment_status', 'paid')
                      ->where('created_at', '>=', now()->subDays($period));
            })
            ->selectRaw('DATE(order_items.created_at) as date, SUM(subtotal) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Product performance
        $productPerformance = Product::where('owner_id', $sellerId)
            ->withCount('orderItems')
            ->withSum('orderItems', 'subtotal')
            ->orderBy('order_items_sum_subtotal', 'desc')
            ->limit(10)
            ->get();

        // Category breakdown
        $categoryBreakdown = Product::where('owner_id', $sellerId)
            ->selectRaw('category, COUNT(*) as count, SUM(stock) as total_stock')
            ->groupBy('category')
            ->get();

        return response()->json([
            'revenue_timeline' => $revenue,
            'product_performance' => $productPerformance,
            'category_breakdown' => $categoryBreakdown,
        ]);
    }

    /**
     * Get inventory alerts (low stock products)
     */
    public function inventoryAlerts(Request $request)
    {
        $threshold = $request->get('threshold', 10);

        $lowStock = Product::where('owner_id', $request->user()->id)
            ->where('stock', '>', 0)
            ->where('stock', '<=', $threshold)
            ->where('is_active', true)
            ->orderBy('stock', 'asc')
            ->get();

        $outOfStock = Product::where('owner_id', $request->user()->id)
            ->where('stock', 0)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
        ]);
    }
}
