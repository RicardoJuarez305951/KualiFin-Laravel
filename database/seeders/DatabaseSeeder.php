<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            DemoCoreUserSeeder::class,
            DemoHierarchySeeder::class,
            DemoClientSeeder::class,
            // RealisticFilterCasesSeeder::class,
        ]);
    }
}
