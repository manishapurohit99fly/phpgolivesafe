<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['category_name' => 'Security',      'sort_order' => 1],
            ['category_name' => 'Migration',     'sort_order' => 2],
            ['category_name' => 'Configuration', 'sort_order' => 3],
            ['category_name' => 'Testing',       'sort_order' => 4],
            ['category_name' => 'Deployment',    'sort_order' => 5],
        ];

        DB::table(config('tables.checklist_categories'))->insertOrIgnore($categories);
    }
}
