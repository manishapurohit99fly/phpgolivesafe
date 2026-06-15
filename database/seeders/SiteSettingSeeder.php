<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['setting_key' => 'site_name',              'setting_value' => 'PHPGoLiveSafe'],
            ['setting_key' => 'site_logo',              'setting_value' => ''],
            ['setting_key' => 'auth_side_banner',       'setting_value' => ''],
            ['setting_key' => 'two_factor_enabled',     'setting_value' => '0'],
            ['setting_key' => 'admin_theme_colour',     'setting_value' => '#0f172a'],
            ['setting_key' => 'admin_secondary_colour', 'setting_value' => '#2563eb'],
            ['setting_key' => 'facebook_url',           'setting_value' => ''],
            ['setting_key' => 'instagram_url',          'setting_value' => ''],
            ['setting_key' => 'linkedin_url',           'setting_value' => ''],
            ['setting_key' => 'twitter_url',            'setting_value' => ''],
            ['setting_key' => 'web_site_name',          'setting_value' => 'Base'],
            ['setting_key' => 'web_site_email',         'setting_value' => 'base@example.com'],
            ['setting_key' => 'web_site_logo',          'setting_value' => ''],
            ['setting_key' => 'web_site_logo',          'setting_value' => ''],
            ['setting_key' => 'web_favicon',            'setting_value' => ''],
            ['setting_key' => 'web_website_status',     'setting_value' => 'live'],
            ['setting_key' => 'web_timezone',           'setting_value' => ''],
            ['setting_key' => 'web_front_page',         'setting_value' => 'home'],
            ['setting_key' => 'web_header_layout',      'setting_value' => 'normal'],
            ['setting_key' => 'web_header_position',    'setting_value' => 'static'],
            ['setting_key' => 'web_footer_copyright_1', 'setting_value' => 'copyright @ 2024 Fliply. All rights reserved.'],
            ['setting_key' => 'web_footer_copyright_2', 'setting_value' => 'SSL Secure · APP Compliant · AU Hosted'],
            ['setting_key' => 'web_footer_about_title', 'setting_value' => 'The property flipping platform for Australian investors. From first idea to settlement — all in one place.'],
            ['setting_key' => 'web_facebook_url',       'setting_value' => 'https://www.facebook.com/fliply'],
            ['setting_key' => 'web_instagram_url',      'setting_value' => 'https://www.instagram.com/fliply'],
            ['setting_key' => 'web_linkedin_url',       'setting_value' => 'https://www.linkedin.com/company/fliply'],
            ['setting_key' => 'web_twitter_url',        'setting_value' => 'https://www.x.com/fliply'],
            ['setting_key' => 'web_smtp_host',          'setting_value' => 'smtp.gmail.com'],
            ['setting_key' => 'web_smtp_port',          'setting_value' => '587'],
            ['setting_key' => 'web_smtp_username',      'setting_value' => 'your_email@gmail.com'],
            ['setting_key' => 'web_smtp_password',      'setting_value' => 'your_password'],
            ['setting_key' => 'web_header_menu',        'setting_value' => 'header-menu'],



        ];

        $table = config('tables.site_settings');

        foreach ($settings as $setting) {
            DB::table($table)->updateOrInsert(
                ['setting_key' => $setting['setting_key']],
                ['setting_value' => $setting['setting_value']]
            );
        }
    }
}
