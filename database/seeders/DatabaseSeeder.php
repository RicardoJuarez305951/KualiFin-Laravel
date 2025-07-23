<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $roles = ['promotor', 'administrador', 'supervisor', 'ejecutivo', 'superadmin'];

        foreach (range(1, 20) as $index) {
            User::factory()->create([
                'rol' => $roles[array_rand($roles)],
                'password' => ('Password123'),
            ]);
        }

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'rol' => 'superadmin',
            'telefono' => '1234567890',
        ]);
    }
}
