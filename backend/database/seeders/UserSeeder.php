<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();

        User::create([
            'name' => 'Admin CESIZen',
            'email' => 'admin@cesizen.com',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'active' => true
        ]);

        User::create([
            'name' => 'Utilisateur Test',
            'email' => 'user@cesizen.com',
            'password' => Hash::make('password123'),
            'role_id' => $userRole->id,
            'active' => true
        ]);
    }
}