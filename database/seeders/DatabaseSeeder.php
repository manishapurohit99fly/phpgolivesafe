<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            RolesSeeder::class,
            UserSeeder::class,
            // CountriesSeeder::class,
            OtpTemplateSeeder::class,
            EmailTemplateSeeder::class,
            SiteSettingSeeder::class,
            ManageUsersSeeder::class,
            FaqSeeder::class,
            TestSeeder::class,
            TechStackSeeder::class,
            DummyDataSeeder::class,
        ]);
    }
}
