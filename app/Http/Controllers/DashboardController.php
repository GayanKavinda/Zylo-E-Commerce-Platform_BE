<?php

namespace App\Http\Controllers;

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
}