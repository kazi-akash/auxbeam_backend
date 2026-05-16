<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyAdminUsersSeeder extends Seeder
{
    /**
     * Seed dummy admin users covering all ecommerce roles.
     *
     * Password for all users: 12345678
     */
    public function run(): void
    {
        $password = Hash::make('12345678');

        $users = [
            [
                'first_name' => 'Sarah',
                'last_name'  => 'Mitchell',
                'email'      => 'superadmin@auxbeam.com',
                'phone'      => '+1-555-100-0001',
                'role'       => 'super_admin',
            ],
            [
                'first_name' => 'James',
                'last_name'  => 'Carter',
                'email'      => 'orders@auxbeam.com',
                'phone'      => '+1-555-100-0002',
                'role'       => 'order_manager',
            ],
            [
                'first_name' => 'Emily',
                'last_name'  => 'Nguyen',
                'email'      => 'inventory@auxbeam.com',
                'phone'      => '+1-555-100-0003',
                'role'       => 'inventory_manager',
            ],
            [
                'first_name' => 'Marcus',
                'last_name'  => 'Thompson',
                'email'      => 'marketing@auxbeam.com',
                'phone'      => '+1-555-100-0004',
                'role'       => 'marketing_manager',
            ],
            [
                'first_name' => 'Linda',
                'last_name'  => 'Patel',
                'email'      => 'accounts@auxbeam.com',
                'phone'      => '+1-555-100-0005',
                'role'       => 'accountant',
            ],
        ];

        foreach ($users as $data) {
            $roleName = $data['role'];
            unset($data['role']);

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'user_type'          => 'admin',
                    'password'           => $password,
                    'email_verified_at'  => now(),
                    'status'             => true,
                ])
            );

            $role = Role::where('name', $roleName)->first();

            if ($role) {
                $user->assignRole($role);
                $this->command->info("Created user [{$data['email']}] with role [{$role->display_name}].");
            } else {
                $this->command->warn("Role [{$roleName}] not found. Run RBACSeeder first.");
            }
        }

        $this->command->info('');
        $this->command->info('Dummy admin users seeded. Password for all: 12345678');
        $this->command->table(
            ['Name', 'Email', 'Role'],
            collect($users)->map(fn ($u) => [
                "{$u['first_name']} {$u['last_name']}",
                $u['email'],
                match (true) {
                    str_contains($u['email'], 'superadmin')  => 'Super Admin',
                    str_contains($u['email'], 'orders')      => 'Order Manager',
                    str_contains($u['email'], 'inventory')   => 'Inventory Manager',
                    str_contains($u['email'], 'marketing')   => 'Marketing Manager',
                    str_contains($u['email'], 'accounts')    => 'Accountant',
                    default                                  => '—',
                },
            ])->toArray()
        );
    }
}
