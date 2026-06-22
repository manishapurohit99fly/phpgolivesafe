<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    private int $adminId;
    private array $userIds  = [];

    // Checklist item IDs per category, keyed by tech stack
    private array $items = [
        // tech_stack_id = 1 (Laravel)
        1 => [
            'code_prep'  => [196, 197, 198, 199, 200, 201, 202, 203],
            'testing'    => [204, 205, 206],
            'server'     => [207, 208, 209, 210, 211, 212],
            'env_config' => [213, 214, 215, 216, 217, 218, 219, 220, 221, 222],
            'security'   => [223, 224, 225, 226],
            'database'   => [227, 228, 229],
            'deployment' => [230, 231, 232, 233, 234, 235, 236, 237, 238, 239, 240, 241],
            'validation' => [242, 243, 244, 245, 246, 247, 248, 249, 250],
            'monitoring' => [251, 252, 253, 254, 255, 256],
            'seo'        => [257, 258, 259],
            'closure'    => [269, 270, 271, 272],
        ],
        // tech_stack_id = 2 (WordPress)
        2 => [
            'server'      => [273, 274, 275, 276, 277],
            'security'    => [278, 279, 280, 281, 282, 283, 284, 285, 286, 287, 288],
            'cleanup'     => [289, 290, 291, 292, 293],
            'backup'      => [294, 295, 296, 297],
            'email'       => [298, 299, 300],
            'performance' => [301, 302, 303, 304, 305, 306, 307, 308, 309],
            'seo'         => [310, 311, 312, 313, 314],
            'validation'  => [315, 316, 317, 318, 319, 320, 321],
            'plugins'     => [322, 323, 324, 325, 326, 327, 328, 329, 330],
        ],
    ];

    public function run(): void
    {
        $this->command->info('Creating dummy users...');
        $this->createUsers();

        $this->command->info('Creating dummy projects & assessments...');
        $this->createProjects();

        $this->command->info('Dummy data seeded successfully.');
        $this->command->table(
            ['Type', 'Count'],
            [
                ['Dummy Admin', 1],
                ['Dummy Users', count($this->userIds)],
                ['Projects',    10],
                ['Assessments', '24 (mix of completed / in-progress / pending)'],
            ]
        );
    }

    // ------------------------------------------------------------------
    // Users
    // ------------------------------------------------------------------

    private function createUsers(): void
    {
        $tbl = config('tables.users');

        $this->adminId = DB::table($tbl)->insertGetId([
            'first_name' => 'James',
            'last_name'  => 'Thompson',
            'email'      => 'dummy.admin@test.com',
            'password'   => Hash::make('Password@123'),
            'role'       => 1,
            'status'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $dummies = [
            ['first_name' => 'Sarah',  'last_name' => 'Johnson',  'email' => 'dummy.user1@test.com'],
            ['first_name' => 'Mark',   'last_name' => 'Davis',    'email' => 'dummy.user2@test.com'],
            ['first_name' => 'Emily',  'last_name' => 'Chen',     'email' => 'dummy.user3@test.com'],
            ['first_name' => 'David',  'last_name' => 'Wilson',   'email' => 'dummy.user4@test.com'],
            ['first_name' => 'Lisa',   'last_name' => 'Martinez', 'email' => 'dummy.user5@test.com'],
        ];

        foreach ($dummies as $u) {
            $this->userIds[] = DB::table($tbl)->insertGetId([
                'first_name' => $u['first_name'],
                'last_name'  => $u['last_name'],
                'email'      => $u['email'],
                'password'   => Hash::make('Password@123'),
                'role'       => 2,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    // ------------------------------------------------------------------
    // Projects
    // ------------------------------------------------------------------

    private function createProjects(): void
    {
        // Each entry: project fields + assessments definition
        $definitions = [
            // ── Laravel projects ──────────────────────────────────────
            [
                'project_name'        => 'E-Commerce Platform',
                'project_description' => 'Full-featured e-commerce platform with cart, checkout, and payment gateway integration.',
                'client_name'         => 'ShopCo',
                'project_url'         => 'https://shopco.example.com',
                'tech_stack_id'       => 1,
                'status'              => 1,
                'users'               => [0, 1],   // indices into $this->userIds
                'assessments'         => [
                    ['name' => 'v1.0 Initial Launch',      'state' => 'completed',   'daysAgo' => 60],
                    ['name' => 'v1.5 Payment Integration', 'state' => 'completed',   'daysAgo' => 30],
                    ['name' => 'v2.0 Feature Update',      'state' => 'in_progress', 'daysAgo' => 10],
                ],
            ],
            [
                'project_name'        => 'CRM System',
                'project_description' => 'Customer relationship management system for field and inside sales teams.',
                'client_name'         => 'SalesFirm',
                'project_url'         => 'https://crm.salesfirm.example.com',
                'tech_stack_id'       => 1,
                'status'              => 1,
                'users'               => [1, 2],
                'assessments'         => [
                    ['name' => 'v1.0 Production Deploy', 'state' => 'completed',   'daysAgo' => 45],
                    ['name' => 'v1.2 Hotfix Release',    'state' => 'in_progress', 'daysAgo' => 8],
                ],
            ],
            [
                'project_name'        => 'HR Management Portal',
                'project_description' => 'Employee management, payroll calculation, and attendance tracking portal.',
                'client_name'         => 'PeopleOps Inc.',
                'project_url'         => 'https://hrportal.peopleops.example.com',
                'tech_stack_id'       => 1,
                'status'              => 1,
                'users'               => [2, 3],
                'assessments'         => [
                    ['name' => 'v1.0 Go Live',      'state' => 'completed',   'daysAgo' => 55],
                    ['name' => 'v1.3 Leave Module', 'state' => 'pending',     'daysAgo' => 5],
                ],
            ],
            [
                'project_name'        => 'Inventory Management',
                'project_description' => 'Stock tracking, warehouse management, and order fulfilment system.',
                'client_name'         => 'StockMart',
                'project_url'         => 'https://inventory.stockmart.example.com',
                'tech_stack_id'       => 1,
                'status'              => 1,
                'users'               => [3, 4],
                'assessments'         => [
                    ['name' => 'v1.0 Beta Deploy',      'state' => 'in_progress', 'daysAgo' => 12],
                    ['name' => 'v1.1 Reporting Add-on', 'state' => 'pending',     'daysAgo' => 3],
                ],
            ],
            [
                'project_name'        => 'API Gateway Service',
                'project_description' => 'Centralised API gateway with rate limiting, caching, and OAuth authentication.',
                'client_name'         => 'TechCorp',
                'project_url'         => 'https://api.techcorp.example.com',
                'tech_stack_id'       => 1,
                'status'              => 0,
                'users'               => [0, 4],
                'assessments'         => [
                    ['name' => 'v1.0 Staging Validation', 'state' => 'in_progress', 'daysAgo' => 7],
                    ['name' => 'v1.0 Production Release',  'state' => 'pending',    'daysAgo' => 1],
                ],
            ],

            // ── WordPress projects ────────────────────────────────────
            [
                'project_name'        => 'Corporate Website',
                'project_description' => 'Corporate website with blog, team profiles, and services pages.',
                'client_name'         => 'GlobalBiz',
                'project_url'         => 'https://globalbiz.example.com',
                'tech_stack_id'       => 2,
                'status'              => 1,
                'users'               => [1, 3],
                'assessments'         => [
                    ['name' => 'Phase 1 – Core Launch', 'state' => 'completed',   'daysAgo' => 70],
                    ['name' => 'Phase 2 – Blog',        'state' => 'completed',   'daysAgo' => 40],
                    ['name' => 'Phase 3 – Services',    'state' => 'in_progress', 'daysAgo' => 6],
                ],
            ],
            [
                'project_name'        => 'Blog Platform',
                'project_description' => 'Multi-author blog with SEO optimisation, tagging, and media library.',
                'client_name'         => 'MediaGroup',
                'project_url'         => 'https://blog.mediagroup.example.com',
                'tech_stack_id'       => 2,
                'status'              => 1,
                'users'               => [0, 2],
                'assessments'         => [
                    ['name' => 'v1.0 Go Live',      'state' => 'completed', 'daysAgo' => 50],
                    ['name' => 'v1.1 SEO Overhaul', 'state' => 'pending',   'daysAgo' => 4],
                ],
            ],
            [
                'project_name'        => 'E-Commerce Store',
                'project_description' => 'WooCommerce-powered online store with Stripe payment integration.',
                'client_name'         => 'RetailBrand',
                'project_url'         => 'https://store.retailbrand.example.com',
                'tech_stack_id'       => 2,
                'status'              => 1,
                'users'               => [2, 4],
                'assessments'         => [
                    ['name' => 'v1.0 Store Launch',    'state' => 'in_progress', 'daysAgo' => 9],
                    ['name' => 'v1.1 Payment Gateway', 'state' => 'pending',     'daysAgo' => 2],
                ],
            ],
            [
                'project_name'        => 'Portfolio Site',
                'project_description' => 'Creative portfolio with project showcase, testimonials, and contact form.',
                'client_name'         => 'CreativeAgency',
                'project_url'         => 'https://portfolio.creativeagency.example.com',
                'tech_stack_id'       => 2,
                'status'              => 1,
                'users'               => [1],
                'assessments'         => [
                    ['name' => 'v1.0 Launch Ready', 'state' => 'completed', 'daysAgo' => 35],
                ],
            ],
            [
                'project_name'        => 'News Portal',
                'project_description' => 'High-traffic news portal with breaking news, categories, and ad slots.',
                'client_name'         => 'NewsDaily',
                'project_url'         => 'https://newsdaily.example.com',
                'tech_stack_id'       => 2,
                'status'              => 1,
                'users'               => [3, 4],
                'assessments'         => [
                    ['name' => 'v1.0 Beta Deploy',   'state' => 'in_progress', 'daysAgo' => 11],
                    ['name' => 'v1.0 Production Go', 'state' => 'pending',     'daysAgo' => 2],
                ],
            ],
        ];

        foreach ($definitions as $def) {
            $this->seedProject($def);
        }
    }

    private function seedProject(array $def): void
    {
        $assessmentDefs = $def['assessments'];
        $userIndices    = $def['users'];
        unset($def['assessments'], $def['users']);

        $projectId = DB::table(config('tables.projects'))->insertGetId(array_merge($def, [
            'created_at' => now()->subDays(90),
            'updated_at' => now(),
        ]));

        // Assign users to project
        $assignedUserIds = array_map(fn ($i) => $this->userIds[$i], $userIndices);
        foreach ($assignedUserIds as $uid) {
            DB::table(config('tables.project_users'))->insertOrIgnore([
                'project_id' => $projectId,
                'user_id'    => $uid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $techStackId = $def['tech_stack_id'];

        foreach ($assessmentDefs as $seq => $aDef) {
            $this->seedAssessment(
                $projectId,
                $techStackId,
                $aDef['name'],
                $aDef['state'],
                $aDef['daysAgo'],
                $assignedUserIds[0],
                $seq
            );
        }
    }

    private function seedAssessment(
        int    $projectId,
        int    $techStackId,
        string $name,
        string $state,
        int    $daysAgo,
        int    $assignedUserId,
        int    $seq
    ): void {
        $createdAt   = now()->subDays($daysAgo);
        $submittedAt = null;

        if ($state === 'completed') {
            $submittedAt = $createdAt->copy()->addDays(rand(2, 5));
        }

        $assessmentId = DB::table(config('tables.assessments'))->insertGetId([
            'project_id'  => $projectId,
            'name'        => $name,
            'description' => 'Deployment assessment for ' . $name . '.',
            'status'      => 1,
            'created_by'  => $this->adminId,
            'submitted_at'=> $submittedAt,
            'created_at'  => $createdAt,
            'updated_at'  => now(),
        ]);

        // Assign one verifier to assessment
        DB::table(config('tables.assessment_users'))->insertOrIgnore([
            'assessment_id' => $assessmentId,
            'user_id'       => $assignedUserId,
        ]);

        // Create shared report link
        DB::table(config('tables.shared_reports'))->insert([
            'project_id'    => $projectId,
            'assessment_id' => $assessmentId,
            'unique_token'  => Str::random(40),
            'created_at'    => $createdAt,
            'updated_at'    => now(),
        ]);

        // Pick checklist items based on state
        $itemsByCategory = $this->items[$techStackId];
        $selectedItems   = $this->selectItems($itemsByCategory, $state);

        // How many items to mark as checked
        $checkedCount = match ($state) {
            'completed'   => count($selectedItems),
            'in_progress' => (int) ceil(count($selectedItems) * 0.55),
            default       => 0,
        };

        foreach ($selectedItems as $idx => $itemId) {
            $isChecked = ($idx < $checkedCount) ? 1 : 0;
            $checkedAt = $isChecked
                ? ($state === 'completed' ? $submittedAt : $createdAt->copy()->addDays(rand(1, 3)))
                : null;

            DB::table(config('tables.assessment_checklists'))->insertOrIgnore([
                'assessment_id'     => $assessmentId,
                'checklist_item_id' => $itemId,
                'is_checked'        => $isChecked,
                'checked_by'        => $isChecked ? $this->adminId : null,
                'checked_at'        => $checkedAt,
                'created_at'        => $createdAt,
                'updated_at'        => now(),
            ]);
        }
    }

    // ------------------------------------------------------------------
    // Item selection strategy per state
    // ------------------------------------------------------------------

    private function selectItems(array $itemsByCategory, string $state): array
    {
        $allKeys = array_keys($itemsByCategory);

        // completed  → all core deployment categories (first 9 or all)
        // in_progress → first ~60% of categories
        // pending    → first 3 categories only
        $take = match ($state) {
            'completed'   => min(9, count($allKeys)),
            'in_progress' => (int) ceil(count($allKeys) * 0.6),
            default       => 3,
        };

        $selected = [];
        foreach (array_slice($allKeys, 0, $take) as $key) {
            foreach ($itemsByCategory[$key] as $id) {
                $selected[] = $id;
            }
        }

        return $selected;
    }
}
