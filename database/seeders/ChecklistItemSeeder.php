<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistItemSeeder extends Seeder
{
    public function run(): void
    {
        $categoryTable = config('tables.checklist_categories');
        $itemTable     = config('tables.checklist_items');

        $categoryId = fn(string $name): int => (int) DB::table($categoryTable)
            ->where('category_name', $name)
            ->value('id');

        $items = [
            // Security
            ['category' => 'Security', 'items' => [
                'Debug Mode Disabled',
                'Environment Variables Secured',
                'Sensitive Files Protected',
            ]],

            // Migration
            ['category' => 'Migration', 'items' => [
                'Database Backup Taken',
                'Migration Script Verified',
                'Rollback Script Ready',
            ]],

            // Configuration
            ['category' => 'Configuration', 'items' => [
                'APP_ENV set to production',
                'Mail Configuration Verified',
                'Cache & Queue Configuration Checked',
            ]],

            // Testing
            ['category' => 'Testing', 'items' => [
                'Smoke Testing Completed',
                'Regression Testing Completed',
                'UAT Completed',
            ]],

            // Deployment
            ['category' => 'Deployment', 'items' => [
                'Production Configuration Checked',
                'Deployment Script Verified',
            ]],
        ];

        $rows = [];
        foreach ($items as $group) {
            $catId = $categoryId($group['category']);
            foreach ($group['items'] as $order => $item) {
                $rows[] = [
                    'category_id'    => $catId,
                    'checklist_item' => $item,
                    'sort_order'     => $order + 1,
                ];
            }
        }

        DB::table($itemTable)->insertOrIgnore($rows);
    }
}
