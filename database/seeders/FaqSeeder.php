<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'question' => 'How do I create a new account?',
                'answer'   => 'Click the Sign Up button on the login page, fill in your details, and submit the form. You will then be able to log in with your email and password.',
                'status'   => 1,
            ],
            [
                'question' => 'I forgot my password. What should I do?',
                'answer'   => 'Use the Forgot Password link on the login screen. Enter your registered email address and follow the instructions sent to your inbox.',
                'status'   => 1,
            ],
            [
                'question' => 'How can I update my profile information?',
                'answer'   => 'From the top-right user menu, open Profile. Update your details and click Update Profile to save changes.',
                'status'   => 1,
            ],
            [
                'question' => 'How do I change my admin password?',
                'answer'   => 'Go to Change Password from the user dropdown. Enter your current password, set a new password, and save.',
                'status'   => 1,
            ],
            [
                'question' => 'Why is my account marked as inactive?',
                'answer'   => 'Accounts can be deactivated by an admin or due to policy reasons. Contact support or your administrator to re-activate your account.',
                'status'   => 1,
            ],
            [
                'question' => 'Can I export the user list?',
                'answer'   => 'Yes. On the Manage Users page, apply filters if needed and click Export CSV. If there are no matching results, export will be disabled.',
                'status'   => 1,
            ],
            [
                'question' => 'How do I add a new CMS page?',
                'answer'   => 'Open CMS Pages from the sidebar, click Add Page, fill in the title/content, and save.',
                'status'   => 1,
            ],
            [
                'question' => 'How do I manage email templates?',
                'answer'   => 'Go to Email Templates from the sidebar. You can add, edit, or delete templates as needed.',
                'status'   => 1,
            ],
            [
                'question' => 'How do I delete a FAQ?',
                'answer'   => 'Open FAQ Manager, click the delete icon for the FAQ item, and confirm the deletion prompt.',
                'status'   => 1,
            ],
            [
                'question' => 'Where do I change site settings?',
                'answer'   => 'Use Site Settings from the sidebar. Update the required fields and save. Changes apply across the admin panel.',
                'status'   => 1,
            ],
            [
                'question' => 'Do you support inactive FAQs?',
                'answer'   => 'Yes. You can keep FAQs inactive (hidden) by setting their status to Inactive. They will not appear in the public FAQ list.',
                'status'   => 0,
            ],
            [
                'question' => 'How can I contact support?',
                'answer'   => 'If your project includes a support email or contact page, use that channel. Otherwise, reach out to your administrator.',
                'status'   => 1,
            ],
        ];

        // Idempotent: avoid inserting duplicates when seeder is re-run.
        foreach ($rows as $row) {
            Faq::query()->firstOrCreate(
                ['question' => $row['question']],
                $row
            );
        }
    }
}

