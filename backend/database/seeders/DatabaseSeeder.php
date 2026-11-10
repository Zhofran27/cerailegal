<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed role ('admin' dan 'warga'), default Super Admin, daftar Pengadilan, dan FAQ
        $this->call([
            RoleAndAdminSeeder::class,
            CourtSeeder::class,
            FaqSeeder::class,
        ]);
    }
}
