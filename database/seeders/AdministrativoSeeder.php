<?php

namespace Database\Seeders;

use App\Models\Administrativo;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdministrativoSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create(['rol' => 'administrativo']);
            Administrativo::create([
                'user_id' => $user->id,
            ]);
            $user->assignRole('administrativo');
        }
    }
}
