<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat 2 role ('admin' dan 'warga')
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $wargaRole = Role::firstOrCreate(['name' => 'warga']);

        // 2. Buat akun super admin default
        $admin = User::firstOrCreate(
            ['email' => 'admin@cerailegal.com'],
            [
                'name' => 'Super Admin CeraiLegal',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Assign role 'admin' ke super admin
        $admin->syncRoles([$adminRole]);
    }
}

