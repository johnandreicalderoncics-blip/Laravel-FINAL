<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Sample employee and customer accounts. The admin account is created
     * by AdminSeeder.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Employee User',
                'email' => 'employee@example.com',
                'role' => User::ROLE_EMPLOYEE,
                'status' => User::STATUS_ACTIVE,
                'phone_number' => '+639170000002',
                'address' => '456 Employee Ave, Employee City',
            ],
            [
                'name' => 'John Customer',
                'email' => 'customer1@example.com',
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_ACTIVE,
                'phone_number' => '+639170000003',
                'address' => '789 Customer Lane, Customer City',
            ],
            [
                'name' => 'Jane Customer',
                'email' => 'customer2@example.com',
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_ACTIVE,
                'phone_number' => '+639170000004',
                'address' => '321 Buyer Blvd, Buyer Town',
            ],
            [
                'name' => 'Inactive Customer',
                'email' => 'inactive@example.com',
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_INACTIVE,
                'phone_number' => '+639170000005',
                'address' => '654 Former Road, Old Town',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [...$user, 'password' => Hash::make('password')],
            );
        }
    }
}
