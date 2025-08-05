<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Promotora;
use App\Models\Supervisor;
use App\Models\Ejecutivo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = ['promotor', 'administrador', 'supervisor', 'ejecutivo', 'superadmin'];

        foreach (range(1, 20) as $index) {
            User::factory()->create([
                'rol' => $roles[array_rand($roles)],
                'password' => Hash::make('Password123'),
            ]);
        }

        // Usuarios específicos
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password123'),
            'rol' => 'superadmin',
            'telefono' => '1234567890',
        ]);

        User::factory()->create([
            'name' => 'Promotora User',
            'email' => 'promotora@example.com',
            'password' => Hash::make('Password123'),
            'rol' => 'promotor',
            'telefono' => '0987654321',
        ]);
    }
}
