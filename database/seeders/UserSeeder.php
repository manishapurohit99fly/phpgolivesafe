<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inputs = [
            [
                'first_name' => 'admin',
                'last_name' => 'admin',
                'email' => 'admin@yopmail.com',
                'password' => Hash::make('admin1234'),
                'dob' => '2000-01-01',
                'role' => 1,
            ],
        ];

        User::insert($inputs);
    }
}
