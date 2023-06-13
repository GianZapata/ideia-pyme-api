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
        $clientRoleName = 'client';

        Role::firstOrCreate(['name' => $userRoleName]);
        Role::firstOrCreate(['name' => $adminRoleName]);
        Role::firstOrCreate(['name' => $clientRoleName]);
    }
}
