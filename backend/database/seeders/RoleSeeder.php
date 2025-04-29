<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create([
            'name' => 'admin',
            'description' => 'Administrateur du système'
        ]);

        Role::create([
            'name' => 'user',
            'description' => 'Utilisateur standard'
        ]);
    }
}