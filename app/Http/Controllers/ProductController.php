<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index() {
        return response()->json([
            'message' => 'Product list (Admins Only)',
            'products' => [
                ['id' => '1', 'name' => 'Banana', 'price' => 250],
                ['id' => '2', 'name' => 'Apple', 'price' => 400],
                ['id' => '3', 'name' => 'Ots', 'price' => 700],
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'reqired|numeric',
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $request->only(['name', 'price']),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'string|max:50',
            'price' => 'numeric'
        ]);

        return response()->json([
            'message' => "Product $id updated successfully",
            'updates' => $request->only(['name','price']),
        ]);
    }

    public function destroy ($id)
    {
        return response()->json([
            'message' => "Product $id delete successfully"
        ]);
    }
}