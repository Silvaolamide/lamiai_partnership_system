<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'program_manager']);
        Role::firstOrCreate(['name' => 'partner']);
        Role::firstOrCreate(['name' => 'customer']);
    }
}