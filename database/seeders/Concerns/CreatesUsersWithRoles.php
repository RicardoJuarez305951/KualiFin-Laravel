<?php

namespace Database\Seeders\Concerns;

use App\Models\User;
use Faker\Generator;
use Illuminate\Support\Facades\Hash;

trait CreatesUsersWithRoles
{
    protected function createUserWithRole(array $attributes, string $role, Generator $faker): User
    {
        $attributes['rol'] = $role;
        $attributes['password'] = $attributes['password'] ?? Hash::make('12345');
        $attributes['telefono'] = $attributes['telefono'] ?? $faker->unique()->numerify('55########');

        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
