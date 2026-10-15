<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'user_role' => User::ROLE_ADMIN,
            'phone_number' => '+1234567890',
            'address' => '123 Admin Street, Admin City',
            'is_active' => true,
        ]);

        // Create Employee User
        User::create([
            'name' => 'Employee User',
            'username' => 'employee',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'user_role' => User::ROLE_EMPLOYEE,
            'phone_number' => '+1234567891',
            'address' => '456 Employee Ave, Employee City',
            'is_active' => true,
        ]);

        // Create Customer Users
        User::create([
            'name' => 'John Customer',
            'username' => 'customer1',
            'email' => 'customer1@example.com',
            'password' => Hash::make('password'),
            'user_role' => User::ROLE_CUSTOMER,
            'phone_number' => '+1234567892',
            'address' => '789 Customer Lane, Customer City',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Jane Customer',
            'username' => 'customer2',
            'email' => 'customer2@example.com',
            'password' => Hash::make('password'),
            'user_role' => User::ROLE_CUSTOMER,
            'phone_number' => '+1234567893',
            'address' => '321 Buyer Blvd, Buyer Town',
            'is_active' => true,
        ]);
    }
}
