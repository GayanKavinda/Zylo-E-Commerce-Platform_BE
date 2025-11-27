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
        $query = OrderItem::where('seller_id', $request->user()->id)
            ->with(['order.user', 'product']);

        // Filter by fulfillment status
        if ($request->has('fulfillment_status')) {
            $query->where('fulfillment_status', $request->fulfillment_status);
        }

        // Filter by order status
        if ($request->has('order_status')) {
            $query->whereHas('order', function($q) use ($request) {
                $q->where('status', $request->order_status);
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($orders);
    }

    /**
     * Update order item fulfillment status
     */
    public function updateFulfillmentStatus(Request $request, $id)
    {
        $request->validate([
            'fulfillment_status' => 'required|in:pending,processing,shipped,delivered',
        ]);

        $orderItem = OrderItem::where('id', $id)
            ->where('seller_id', $request->user()->id)
            ->firstOrFail();

        $orderItem->update(['fulfillment_status' => $request->fulfillment_status]);

        return response()->json([
            'message' => 'Fulfillment status updated successfully',
            'order_item' => $orderItem->load(['order', 'product']),
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
