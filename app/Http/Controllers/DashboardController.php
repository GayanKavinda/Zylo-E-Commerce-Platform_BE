<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->user()->role;
        return response()->json([
            'message' => "Dashboard for $role",
        ]);
    }

    public function stats()
    {
        return response()->json([
            'totalCustomers' => User::where('role', 'customer')->count(),
            'totalProducts' => 0,
            'totalOrders' => 0,
        ]);
    }
}