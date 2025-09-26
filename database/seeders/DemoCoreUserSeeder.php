<?php

namespace Database\Seeders;

use App\Models\Administrativo;
use App\Models\TipoDocumento;
use Database\Seeders\Concerns\CreatesUsersWithRoles;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCoreUserSeeder extends Seeder
{
    use CreatesUsersWithRoles;

    public function run(): void
    {
        $faker = fake();

        $this->createUserWithRole([
            'name' => 'Supervisor User Demo',
            'email' => 'superadmin@example.com',
            'telefono' => '5550100000',
            'password' => Hash::make('12345'),
        ], 'superadmin', $faker);

        $adminUser = $this->createUserWithRole([
            'name' => 'Administrativo User Demo',
            'email' => 'admin@example.com',
            'telefono' => '5550100001',
            'password' => Hash::make('12345'),
        ], 'administrador', $faker);

        Administrativo::create([
            'user_id' => $adminUser->id,
        ]);

        TipoDocumento::firstOrCreate(['nombre' => 'default']);
    }
}
