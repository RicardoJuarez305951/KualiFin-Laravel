<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Promotor;
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
            $user = User::factory()->create([
                'password' => Hash::make('Password123'),
            ]);
            $user->assignRole($roles[array_rand($roles)]);
        }

        // Usuarios específicos
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password123'),
            'telefono' => '1234567890',
        ]);
        $user->assignRole('superadmin');

        $user = User::factory()->create([
            'name' => 'Promotor User',
            'email' => 'promotor@example.com',
            'password' => Hash::make('Password123'),
            'telefono' => '0987654321',
        ]);
        $user->assignRole('promotor');

        $user = User::factory()->create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@example.com',
            'password' => Hash::make('Password123'),
            'telefono' => '0987654321',
        ]);
        $user->assignRole('supervisor');

        $user = User::factory()->create([
            'name' => 'Ejecutivo User',
            'email' => 'ejecutivo@example.com',
            'password' => Hash::make('Password123'),
            'telefono' => '0987654321',
        ]);
        $user->assignRole('ejecutivo');
    }
}
