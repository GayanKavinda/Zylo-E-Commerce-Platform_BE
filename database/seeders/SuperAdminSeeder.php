<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin user
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);

        // Assign superadmin role (optional, using simple role field primarily)
        $role = Role::where('name', 'superadmin')->first();
        if ($role) {
            $superadmin->assignRole($role);
        }

        $this->command->info('Super Admin created: superadmin@example.com / password');
    }
}
