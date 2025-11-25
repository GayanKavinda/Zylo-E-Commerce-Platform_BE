<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Admin User
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            [
                'email' => 'customer@example.com'
            ],
            [
                'name' => 'Customer User',
                'password' => Hash::make('password'),
                'role' => 'customer'
            ]
        );

        User::updateOrCreate(
            [
                'email' => 'seller@example.com'
            ],
            [
                'name' => 'Test Seller',
                'password' => Hash::make('password'),
                'role' => 'seller'
            ]  
        );
    }
}
