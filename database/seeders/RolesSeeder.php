<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Seeds the canonical role rows used by `users.role` (1=Admin, 3=User).
     * Idempotent so repeated runs don't duplicate data.
     */
    public function run(): void
    {
        $rolesTable = config('tables.roles');

        $roles = [
            ['id' => 1, 'name' => 'Admin'],
            ['id' => 2, 'name' => 'User'],
            ['id' => 3, 'name' => 'Verification User'],
        ];

        foreach ($roles as $role) {
            DB::table($rolesTable)->updateOrInsert(
                ['id' => $role['id']],
                [
                    'name'       => $role['name'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
