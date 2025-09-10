<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [];
        for ($i = 1; $i <= 20; $i++) {
            $permissions[] = Permission::create(['name' => 'permission'.$i]);
        }

        $roleNames = [
            'superadmin',
            'administrador',
            'supervisor',
            'ejecutivo',
            'promotor',
        ];
        for ($i = 1; $i <= 15; $i++) {
            $roleNames[] = 'role'.$i;
        }

        $roles = [];
        foreach ($roleNames as $roleName) {
            $roles[] = Role::create(['name' => $roleName]);
        }

        foreach ($roles as $role) {
            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission);
            }
        }
    }
}

