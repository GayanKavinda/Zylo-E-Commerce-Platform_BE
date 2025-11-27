<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Get the user's cart
     */
    public function index(Request $request)
    {
        $cartItems = CartItem::where('user_id', $request->user()->id)
            ->with(['product.owner', 'product.reviews'])
            ->get();

        $total = 0;
        $itemsWithDetails = $cartItems->map(function ($item) use (&$total) {
            $price = $item->product->discount_price ?? $item->product->price;
            $subtotal = $price * $item->quantity;
            $total += $subtotal;

            return [
                'id' => $item->id,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'price' => $item->product->price,
                    'discount_price' => $item->product->discount_price,
                    'effective_price' => $price,
                    'images' => $item->product->images,
                    'stock' => $item->product->stock,
                    'category' => $item->product->category,
                    'average_rating' => $item->product->average_rating,
                    'reviews_count' => $item->product->reviews_count,
                    'owner' => [
                        'id' => $item->product->owner->id,
                        'name' => $item->product->owner->name,
                    ],
                ],
                'quantity' => $item->quantity,
                'subtotal' => $subtotal,
            ];
        });

        return response()->json([
            'cart_items' => $itemsWithDetails,
            'total' => $total,
            'items_count' => $cartItems->count(),
        ]);
    }

    /**
     * Add item to cart
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check if product is active
        if (!$product->is_active) {
            return response()->json([
                'message' => 'This product is not available for purchase.'
            ], 400);
        }

        // Check stock availability
        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Insufficient stock. Only ' . $product->stock . ' items available.'
            ], 400);
        }

        // Check if item already in cart
        $cartItem = CartItem::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem->quantity + $request->quantity;
            
            if ($product->stock < $newQuantity) {
                return response()->json([
                    'message' => 'Cannot add more items. Stock limit reached.'
                ], 400);
            }

            $cartItem->update(['quantity' => $newQuantity]);
            $message = 'Cart updated successfully';
        } else {
            // Create new cart item
            $cartItem = CartItem::create([
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
            $message = 'Item added to cart successfully';
        }

        return response()->json([
            'message' => $message,
            'cart_item' => $cartItem->load('product'),
        ], 201);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $product = $cartItem->product;

        // Check stock availability
        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Insufficient stock. Only ' . $product->stock . ' items available.'
            ], 400);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'message' => 'Cart item updated successfully',
            'cart_item' => $cartItem->load('product'),
        ]);
    }

    /**
     * Remove item from cart
     */
    public function destroy(Request $request, $id)
    {
        $cartItem = CartItem::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $cartItem->delete();

        return response()->json([
            'message' => 'Item removed from cart successfully',
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear(Request $request)
    {
        CartItem::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'message' => 'Cart cleared successfully',
        ]);
    }
}
