<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Config::get('permission.roles');
        foreach ($roles as $key => $role) {
            if ($role == 'Member') {
                Role::create([
                    'guard_name' => Str::lower($role),
                    'name'       => $role,
                ]);
                Role::create([
                    'guard_name' => Str::lower($role) . '-api',
                    'name'       => $role,
                ]);
            } else {
                Role::create(['name' => $role, 'guard_name' => 'user']);
                Role::create(['name' => $role, 'guard_name' => 'user-api']);
            }
        }

        // Add User
        User::create([
            'name'     => [
                'en' => 'SuperAdmin',
                'gu' => '',
            ],
            'email'    => 'superadmin@parivar.com',
            'password' => Hash::make('Admin@123'),
        ])->assignRole('SuperAdmin');
        User::create([
            'name'     => [
                'en' => 'Admin',
                'gu' => '',
            ],
            'email'    => 'admin@parivar.com',
            'password' => Hash::make('Admin@123'),
        ])->assignRole('Admin');
        User::create([
            'name'     => [
                'en' => 'SubAdmin',
                'gu' => '',
            ],
            'email'    => 'subadmin@parivar.com',
            'password' => Hash::make('Admin@123'),
        ])->assignRole('SubAdmin');
    }
}
