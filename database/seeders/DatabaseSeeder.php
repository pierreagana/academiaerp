<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default Super Admin Account
        // Credentials: admin@academiaerp.com / SuperAdmin2026!
        User::firstOrCreate(
            ['email' => 'admin@academiaerp.com'],
            [
                'name'     => 'Super Administrateur',
                'email'    => 'admin@academiaerp.com',
                'password' => Hash::make('SuperAdmin2026!'),
                'role'     => 'superadmin',
            ]
        );

        $this->call([
            SuperAdminSeeder::class,
            SuperAdminLot2Seeder::class,
            SuperAdminLot3Seeder::class,
            SuperAdminLot4Seeder::class,
            SuperAdminLot5Seeder::class,
            PermissionSeeder::class,
        ]);
    }
}
