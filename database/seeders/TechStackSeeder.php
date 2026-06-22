<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TechStackSeeder extends Seeder
{
    public function run(): void
    {
        $catTable  = config('tables.checklist_categories');
        $itemTable = config('tables.checklist_items');

        // Remove old project/assessment checklist rows first (FK constraint)
        DB::table(config('tables.project_checklists'))->delete();
        DB::table(config('tables.assessment_checklists'))->delete();

        // Clear all existing categories & items
        DB::table($itemTable)->delete();
        DB::table($catTable)->delete();

        // Ensure tech stacks exist (id 1 = Laravel, id 2 = WordPress)
        DB::table(config('tables.tech_stacks'))->insertOrIgnore([
            ['id' => 1, 'name' => 'Laravel',    'status' => 1, 'sort_order' => 1],
            ['id' => 2, 'name' => 'WordPress',  'status' => 1, 'sort_order' => 2],
        ]);

        // ----------------------------------------------------------------
        // Laravel checklist
        // ----------------------------------------------------------------
        $laravel = [
            [
                'title' => 'Code & Release Preparation',
                'tasks' => [
                    'Code freeze completed',
                    'Release branch prepared',
                    'Debugging code removed',
                    'Remove backup files / archives / commented code',
                    'Remove hardcoded credentials',
                    'Code quality checks passed',
                    'Production branding verified (logo, favicon, etc.)',
                    'Contact details verified',
                ],
            ],
            [
                'title' => 'Testing & Approval',
                'tasks' => [
                    'Unit tests passed',
                    'UAT testing completed',
                    'UAT approval received',
                ],
            ],
            [
                'title' => 'Server & Environment Setup',
                'tasks' => [
                    'Production server accessible',
                    'PHP version compatible',
                    'Required PHP extensions installed',
                    'Composer installed',
                    'Disk space sufficient',
                    'SSL certificate verified',
                ],
            ],
            [
                'title' => 'Environment Configuration',
                'tasks' => [
                    '.env file reviewed',
                    'APP_ENV set to production',
                    'APP_DEBUG set to false',
                    'App key generated (APP_KEY)',
                    'Database credentials verified',
                    'Cache/Redis configuration verified',
                    'Queue configuration verified',
                    'Mail configuration verified',
                    'Third-party API credentials verified',
                    'Debugbar disabled',
                ],
            ],
            [
                'title' => 'Security',
                'tasks' => [
                    'Environment variables secured',
                    'Sensitive files protected (.env, storage, logs)',
                    'File permissions correctly set',
                    'Directory permissions correctly set',
                ],
            ],
            [
                'title' => 'Database Preparation',
                'tasks' => [
                    'Production database backup completed',
                    'Migration changes reviewed',
                    'Database rollback plan verified',
                ],
            ],
            [
                'title' => 'Deployment Execution',
                'tasks' => [
                    'Enable maintenance mode (php artisan down)',
                    'Pull latest code (git pull)',
                    'Install dependencies (composer install --no-dev --optimize-autoloader)',
                    'Run migrations (php artisan migrate --force)',
                    'Clear old cache (php artisan optimize:clear)',
                    'Cache config (php artisan config:cache)',
                    'Cache routes (php artisan route:cache)',
                    'Cache views (php artisan view:cache)',
                    'Cache events (php artisan event:cache)',
                    'Restart queues (php artisan queue:restart)',
                    'Restart horizon (php artisan horizon:terminate if used)',
                    'Disable maintenance mode (php artisan up)',
                ],
            ],
            [
                'title' => 'Post-Deployment Validation',
                'tasks' => [
                    'Homepage accessible',
                    'Login and logout working',
                    'Critical business flows verified',
                    'API endpoints responding',
                    'Queue jobs processing',
                    'Scheduler running (cron working)',
                    'Email functionality verified',
                    'File upload/download working',
                    'No critical errors in logs',
                ],
            ],
            [
                'title' => 'Monitoring',
                'tasks' => [
                    'Review Laravel logs',
                    'Review server logs (Apache/Nginx)',
                    'Check queue failures',
                    'Database health verified',
                    'CPU and memory usage normal',
                    'No customer-impacting issues reported',
                ],
            ],
            [
                'title' => 'SEO & Public Configuration',
                'tasks' => [
                    'Remove noindex/nofollow if applied',
                    'Verify meta titles and descriptions',
                    'Verify sitemap and robots.txt',
                ],
            ],
            [
                'title' => 'Rollback Readiness',
                'tasks' => [
                    'Previous stable release available',
                    'Rollback steps documented',
                    'Rollback tested or verified',
                ],
            ],
            [
                'title' => 'Rollback Execution (If Required)',
                'tasks' => [
                    'Enable maintenance mode',
                    'Checkout previous release (git checkout)',
                    'Install dependencies',
                    'Clear cache',
                    'Restart queues',
                    'Bring application back online',
                ],
            ],
            [
                'title' => 'Deployment Closure',
                'tasks' => [
                    'Deployment marked successful',
                    'Monitoring completed',
                    'Issues documented if any',
                    'Client informed',
                ],
            ],
        ];

        // ----------------------------------------------------------------
        // WordPress checklist
        // ----------------------------------------------------------------
        $wordpress = [
            [
                'title' => 'Server & Software Upgrades',
                'tasks' => [
                    'Upgrade PHP to the latest supported version',
                    'Upgrade MySQL/MariaDB to the latest supported version',
                    'Verify Apache/Nginx configuration and compatibility',
                    'Update WordPress core to the latest stable version',
                    'Update all themes and plugins to their latest versions',
                ],
            ],
            [
                'title' => 'Security Hardening',
                'tasks' => [
                    'Configure secure file and folder permissions',
                    'Install and configure a web application firewall',
                    'Enable brute-force attack protection and login lockout',
                    'Enable file change detection and malware scanning',
                    'Protect against pingback and XML-RPC vulnerabilities',
                    'Hide or secure the WordPress login URL',
                    'Hide WordPress version and branding where applicable',
                    'Disable unused REST API endpoints if not required',
                    'Hide or disable unused registration and public URLs',
                    'Configure CAPTCHA on login, registration, and contact forms',
                    'Review and remove unnecessary administrator accounts',
                ],
            ],
            [
                'title' => 'Cleanup & Maintenance',
                'tasks' => [
                    'Remove unused plugins, themes, files, and folders',
                    'Remove old backups and temporary files from the server',
                    'Delete test/demo content if applicable',
                    'Review and clean the media library if required',
                    'Disable automatic updates for plugins and themes (based on project requirements)',
                ],
            ],
            [
                'title' => 'Backup & Recovery',
                'tasks' => [
                    'Install and configure a backup solution',
                    'Configure manual and scheduled backups',
                    'Verify backup restoration procedures',
                    'Store backups in a secure off-site location',
                ],
            ],
            [
                'title' => 'Email Configuration',
                'tasks' => [
                    'Configure SMTP for reliable email delivery',
                    'Test contact forms and system-generated emails',
                    'Update email templates to match website branding',
                ],
            ],
            [
                'title' => 'Performance Optimization',
                'tasks' => [
                    'Enable page caching',
                    'Minify CSS, JavaScript, and HTML files',
                    'Enable GZIP/Brotli compression',
                    'Optimize and compress images',
                    'Enable lazy loading for images and videos',
                    'Combine CSS and JavaScript files where appropriate',
                    'Configure browser caching',
                    'Review database performance and optimize tables',
                    'Test website speed and Core Web Vitals',
                ],
            ],
            [
                'title' => 'SEO & Search Engine Readiness',
                'tasks' => [
                    'Verify SEO plugin configuration',
                    'Generate and submit XML sitemap',
                    'Configure meta titles and descriptions',
                    'Verify robots.txt configuration',
                    'Remove any staging/no-index settings before launch',
                ],
            ],
            [
                'title' => 'Live Environment Validation',
                'tasks' => [
                    'Update domain URLs from staging to production',
                    'Configure SSL certificate and force HTTPS',
                    'Verify all forms, payment gateways, and integrations',
                    'Test user registration, login, and password reset functionality',
                    'Verify mobile responsiveness',
                    'Perform final cross-browser testing',
                    'Monitor error logs after deployment',
                ],
            ],
            [
                'title' => 'Recommended Plugins',
                'tasks' => [
                    'SMTP Plugin (Post SMTP)',
                    'Security Plugin (Wordfence or equivalent)',
                    'Image Optimization Plugin',
                    'SEO Plugin (Yoast SEO or equivalent)',
                    'Backup Plugin',
                    'Cache/Performance Plugin',
                    'Login URL Protection Plugin',
                    'Email Template Customization Plugin',
                    'Sitemap Plugin (if not provided by SEO plugin)',
                ],
            ],
        ];

        $this->insertStack(1, $laravel, $catTable, $itemTable);
        $this->insertStack(2, $wordpress, $catTable, $itemTable);
    }

    private function insertStack(int $techStackId, array $categories, string $catTable, string $itemTable): void
    {
        foreach ($categories as $order => $cat) {
            $catId = DB::table($catTable)->insertGetId([
                'tech_stack_id' => $techStackId,
                'category_name' => $cat['title'],
                'sort_order'    => $order + 1,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            foreach ($cat['tasks'] as $itemOrder => $task) {
                DB::table($itemTable)->insert([
                    'category_id'    => $catId,
                    'checklist_item' => $task,
                    'sort_order'     => $itemOrder + 1,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
    }
}
