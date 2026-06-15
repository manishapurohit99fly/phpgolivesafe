<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ManageUsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('User@12345');

        $rows = [
            ['first_name' => 'Aarav',  'last_name' => 'Shah',    'email' => 'aarav.shah@example.com',    'phone_no' => '+91 90000 00001', 'dob' => '1997-02-14'],
            ['first_name' => 'Diya',   'last_name' => 'Patel',   'email' => 'diya.patel@example.com',   'phone_no' => '+91 90000 00002', 'dob' => '1999-06-03'],
            ['first_name' => 'Vivaan', 'last_name' => 'Singh',   'email' => 'vivaan.singh@example.com', 'phone_no' => '+91 90000 00003', 'dob' => '1996-11-22'],
            ['first_name' => 'Isha',   'last_name' => 'Mehta',   'email' => 'isha.mehta@example.com',   'phone_no' => '+91 90000 00004', 'dob' => '1998-08-09'],
            ['first_name' => 'Aditya', 'last_name' => 'Nair',    'email' => 'aditya.nair@example.com',  'phone_no' => '+91 90000 00005', 'dob' => '1995-04-18'],
            ['first_name' => 'Anaya',  'last_name' => 'Reddy',   'email' => 'anaya.reddy@example.com',  'phone_no' => '+91 90000 00006', 'dob' => '2000-01-27'],
            ['first_name' => 'Arjun',  'last_name' => 'Verma',   'email' => 'arjun.verma@example.com',  'phone_no' => '+91 90000 00007', 'dob' => '1994-12-05'],
            ['first_name' => 'Myra',   'last_name' => 'Iyer',    'email' => 'myra.iyer@example.com',    'phone_no' => '+91 90000 00008', 'dob' => '1998-03-30'],
            ['first_name' => 'Kabir',  'last_name' => 'Gupta',   'email' => 'kabir.gupta@example.com',  'phone_no' => '+91 90000 00009', 'dob' => '1997-09-12'],
            ['first_name' => 'Sara',   'last_name' => 'Khan',    'email' => 'sara.khan@example.com',    'phone_no' => '+91 90000 00010', 'dob' => '1996-05-25'],
            ['first_name' => 'Reyansh','last_name' => 'Joshi',   'email' => 'reyansh.joshi@example.com','phone_no' => '+91 90000 00011', 'dob' => '2001-07-07'],
            ['first_name' => 'Kiara',  'last_name' => 'Bose',    'email' => 'kiara.bose@example.com',   'phone_no' => '+91 90000 00012', 'dob' => '1999-10-16'],
        ];

        $payload = array_map(function (array $row) use ($password) {
            return array_merge($row, [
                'password' => $password,
                'device_type' => null,
                'device_id' => null,
                'forgot_token' => null,
                'role' => 2,
                'status' => 1,
            ]);
        }, $rows);

        // Avoid duplicate seeds if run multiple times.
        $emails = array_column($rows, 'email');
        $existing = User::whereIn('email', $emails)->pluck('email')->all();
        $existingMap = array_fill_keys($existing, true);

        $insert = array_values(array_filter($payload, fn ($r) => !isset($existingMap[$r['email']])));
        if (!empty($insert)) {
            User::insert($insert);
        }
    }
}

