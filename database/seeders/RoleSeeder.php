<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userRoleName = 'user';
        $adminRoleName = 'admin';

        $userRole = Role::where('name', $userRoleName)->first();
        if (!$userRole) {
            Role::create(['name' => $userRoleName]);
        }

        $adminRole = Role::where('name', $adminRoleName)->first();
        if (!$adminRole) {
            Role::create(['name' => $adminRoleName]);
        }
    }
}
