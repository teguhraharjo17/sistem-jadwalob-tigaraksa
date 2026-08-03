<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Generator $faker)
    {
        // 1. Seed Roles
        $roles = [
            ['id' => 1, 'name' => 'Super Admin', 'permissions' => json_encode(["dashboard", "checklist", "laporanharian", "master_data"])],
            ['id' => 2, 'name' => 'Admin', 'permissions' => json_encode(["dashboard", "checklist", "laporanharian"])],
            ['id' => 3, 'name' => 'User', 'permissions' => json_encode(["dashboard", "checklist", "laporanharian"])],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id' => $role['id']],
                [
                    'name' => $role['name'], 
                    'permissions' => $role['permissions'],
                    'created_at' => now(), 
                    'updated_at' => now()
                ]
            );
        }

        // 2. Seed default Super Admin
        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'role_id' => 1,
                'password' => Hash::make('superadmin123'),
            ]
        );

        // 3. Seed default Admin
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Teguh',
                'role_id' => 2,
                'password' => Hash::make('admin123'),
            ]
        );

        // 4. Seed default User
        User::updateOrCreate(
            ['username' => 'user'],
            [
                'name' => 'User Staff',
                'role_id' => 3,
                'password' => Hash::make('user123'),
            ]
        );
    }
}
