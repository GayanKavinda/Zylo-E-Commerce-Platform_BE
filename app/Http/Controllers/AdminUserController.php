<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    /**
     * Get all users with their roles
     */
    public function index()
    {
        $users = User::with('roles')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'spatie_roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at,
            ];
        });

        return response()->json($users);
    }

    /**
     * Create a new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:superadmin,admin,seller,customer',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // Assign Spatie role
        if ($role = Role::where('name', $validated['role'])->first()) {
            $user->assignRole($role);
        }

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('roles'),
        ], 201);
    }

    /**
     * Get a single user
     */
    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'spatie_roles' => $user->roles->pluck('name'),
            'created_at' => $user->created_at,
        ]);
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|string|in:superadmin,admin,seller,customer',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        // Update Spatie role if role changed
        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Change user role
     */
    public function changeRole(Request $request, $id)
    {
        $validated = $request->validate([
            'role' => 'required|string|in:superadmin,admin,seller,customer',
        ]);

        $user = User::findOrFail($id);
        
        // Prevent changing your own role
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot change your own role',
            ], 403);
        }

        $user->update(['role' => $validated['role']]);
        $user->syncRoles([$validated['role']]);

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Get all available roles
     */
    public function getRoles()
    {
        $roles = [
            'superadmin' => 'Super Admin - Full platform control',
            'admin' => 'Administrator - Day-to-day operations',
            'seller' => 'Seller - Manage own products',
            'customer' => 'Customer - Browse and purchase',
        ];

        return response()->json($roles);
    }
}
