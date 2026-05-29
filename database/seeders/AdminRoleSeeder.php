<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create super_admin role
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // Create pegawai role for SSO users
        Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);

        $user = User::where('email', 'admin@gmail.com')->first();

        if ($user) {
            $user->assignRole($superAdmin);
        }
    }
}
