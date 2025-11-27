<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Get all products with advanced filtering and search
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['owner:id,name', 'reviews'])
            ->where('is_active', true);

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by rating
        if ($request->has('min_rating')) {
            $minRating = $request->min_rating;
            $query->whereHas('reviews', function($q) use ($minRating) {
                $q->selectRaw('product_id, AVG(rating) as avg_rating')
                  ->groupBy('product_id')
                  ->havingRaw('AVG(rating) >= ?', [$minRating]);
            });
        }

        // Filter by stock availability
        if ($request->boolean('in_stock_only')) {
            $query->where('stock', '>', 0);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        switch ($sortBy) {
            case 'price':
                $query->orderBy('price', $sortOrder);
                break;
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'popularity':
                $query->withCount('orderItems')->orderBy('order_items_count', $sortOrder);
                break;
            case 'rating':
                $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage);

        // Add computed fields
        $products->getCollection()->transform(function($product) {
            $product->average_rating = $product->average_rating;
            $product->reviews_count = $product->reviews_count;
            return $product;
        });

        return response()->json($products);
    }

    /**
     * Get a single product with details
     */
    public function show($id): JsonResponse
    {
        $product = Product::with(['owner:id,name,email', 'reviews.user:id,name'])
            ->findOrFail($id);

        // Increment views
        $product->increment('views');

        $product->average_rating = $product->average_rating;
        $product->reviews_count = $product->reviews_count;

        return response()->json(['product' => $product]);
    }

    /**
     * Create a new product
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'images' => 'nullable|array',
            'images.*' => 'string', // URLs or base64
            'sku' => 'nullable|string|unique:products,sku',
            'is_active' => 'boolean',
        ]);

        // Set owner_id for sellers
        if ($request->user()->role === 'seller') {
            $data['owner_id'] = $request->user()->id;
        } elseif ($request->has('owner_id') && in_array($request->user()->role, ['admin', 'superadmin'])) {
            $data['owner_id'] = $request->owner_id;
        }

        // Generate SKU if not provided
        if (!isset($data['sku'])) {
            $data['sku'] = 'PRD-' . strtoupper(substr(uniqid(), -8));
        }

        $product = Product::create($data);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load('owner:id,name')
        ], 201);
    }

    /**
     * Update a product
     */
    public function update(Request $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        // Check permissions (sellers can only edit their own products)
        if ($request->user()->role === 'seller' && $product->owner_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to update this product'
            ], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'sku' => 'nullable|string|unique:products,sku,' . $id,
            'is_active' => 'boolean',
        ]);

        $product->update($data);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->load('owner:id,name')
        ]);
    }

    /**
     * Delete a product
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        // Check permissions
        if ($request->user()->role === 'seller' && $product->owner_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to delete this product'
            ], 403);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Get product categories
     */
    public function categories(): JsonResponse
    {
        $categories = Product::select('category')
            ->whereNotNull('category')
            ->groupBy('category')
            ->pluck('category');

        return response()->json(['categories' => $categories]);
    }

    /**
     * Get featured products
     */
    public function featured(): JsonResponse
    {
        $products = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->withAvg('reviews', 'rating')
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->limit(8)
            ->get();

        return response()->json(['products' => $products]);
    }

    /**
     * Get related products
     */
    public function related($id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $related = Product::where('id', '!=', $id)
            ->where('is_active', true)
            ->where('category', $product->category)
            ->limit(6)
            ->get();

        return response()->json(['products' => $related]);
    }
}
