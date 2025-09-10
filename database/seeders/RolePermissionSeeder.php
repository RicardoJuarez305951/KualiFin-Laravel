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
            $permissions[] = 'permission_' . $i;
        }
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = ['promotor', 'administrador', 'supervisor', 'ejecutivo', 'administrativo', 'superadmin'];
        for ($i = 1; $i <= 14; $i++) {
            $roles[] = 'role_' . $i;
        }
        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            if ($roleName === 'superadmin') {
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions(Permission::inRandomOrder()->take(5)->get());
            }
        }
    }
}
