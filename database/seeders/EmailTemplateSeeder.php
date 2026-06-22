<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appName = env('APP_NAME', 'App');

        $templates = [
            [
                'name'    => 'forgot_password',
                'subject' => 'Reset Your Password - ' . $appName,
                'body'    => '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
  <table width="600" style="margin:0 auto; background:#ffffff; padding:30px; border-radius:8px;">
    <tr>
      <td>
        <h2 style="color:#333333;">Hello, {{name}}!</h2>
        <p style="color:#555555;">We received a request to reset your password for your <strong>' . $appName . '</strong> account.</p>
        <p style="color:#555555;">Click the button below to reset your password. This link will expire in <strong>60 minutes</strong>.</p>
        <p style="text-align:center; margin:30px 0;">
          <a href="{{link}}"
             style="background:#4F46E5; color:#ffffff; padding:12px 28px; border-radius:5px; text-decoration:none; font-size:15px;">
            Reset Password
          </a>
        </p>
        <p style="color:#555555;">If you did not request a password reset, no action is needed.</p>
        <hr style="border:none; border-top:1px solid #eeeeee; margin:20px 0;">
        <p style="color:#aaaaaa; font-size:12px;">If the button above doesn\'t work, paste this link into your browser:<br>
          <a href="{{link}}" style="color:#4F46E5;">{{link}}</a>
        </p>
      </td>
    </tr>
  </table>
</body>
</html>',
                'status'  => 1,
            ],
            [
                'name'    => 'admin_2fa_code',
                'subject' => 'Your Two-Factor Authentication Code - ' . $appName,
                'body'    => '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
  <table width="600" style="margin:0 auto; background:#ffffff; padding:30px; border-radius:8px;">
    <tr>
      <td>
        <h2 style="color:#333333;">Hello, {{name}}!</h2>
        <p style="color:#555555;">Your two-factor authentication code for <strong>' . $appName . '</strong> is:</p>
        <p style="text-align:center; margin:30px 0;">
          <span style="display:inline-block; font-size:36px; font-weight:bold; letter-spacing:10px; color:#4F46E5; background:#F3F4FF; padding:16px 32px; border-radius:8px;">
            {{otp}}
          </span>
        </p>
        <p style="color:#555555;">This code will expire in <strong>' . config('constants.OTP_EXPIRY_MINUTES') . ' minutes</strong>. Do not share it with anyone.</p>
        <hr style="border:none; border-top:1px solid #eeeeee; margin:20px 0;">
        <p style="color:#aaaaaa; font-size:12px;">If you did not attempt to log in, please secure your account immediately.</p>
      </td>
    </tr>
  </table>
</body>
</html>',
                'status'  => 1,
            ],
            [
                'name'    => 'admin_user_password_reset',
                'subject' => 'Your password has been reset - ' . $appName,
                'body'    => '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
  <table width="600" style="margin:0 auto; background:#ffffff; padding:30px; border-radius:8px;">
    <tr>
      <td>
        <h2 style="color:#333333;">Hello, {{name}}!</h2>
        <p style="color:#555555;">An administrator has reset your password for your <strong>' . $appName . '</strong> account.</p>
        <p style="color:#555555;">Please use the credentials below to log in:</p>
        <table style="margin:20px 0; border-collapse:collapse;">
          <tr>
            <td style="padding:8px 12px; color:#555555;"><strong>Email:</strong></td>
            <td style="padding:8px 12px; color:#333333;">{{email}}</td>
          </tr>
          <tr>
            <td style="padding:8px 12px; color:#555555;"><strong>New Password:</strong></td>
            <td style="padding:8px 12px; color:#333333;">
              <span style="display:inline-block; font-family:monospace; font-size:16px; background:#F3F4FF; color:#4F46E5; padding:6px 12px; border-radius:4px;">{{new_password}}</span>
            </td>
          </tr>
        </table>
        <p style="text-align:center; margin:30px 0;">
          <a href="{{login_url}}"
             style="background:#4F46E5; color:#ffffff; padding:12px 28px; border-radius:5px; text-decoration:none; font-size:15px;">
            Log in now
          </a>
        </p>
        <p style="color:#555555;">For your security, we strongly recommend changing this password after you log in.</p>
        <hr style="border:none; border-top:1px solid #eeeeee; margin:20px 0;">
        <p style="color:#aaaaaa; font-size:12px;">If you did not expect this change, please contact your administrator immediately.</p>
      </td>
    </tr>
  </table>
</body>
</html>',
                'status'  => 1,
            ],
            [
                'name'    => 'welcome',
                'subject' => 'Welcome to ' . $appName . ', {{name}}!',
                'body'    => '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
  <table width="600" style="margin:0 auto; background:#ffffff; padding:30px; border-radius:8px;">
    <tr>
      <td>
        <h2 style="color:#333333;">Welcome, {{name}}!</h2>
        <p style="color:#555555;">Thank you for joining <strong>' . $appName . '</strong>. We\'re excited to have you on board.</p>
        <p style="color:#555555;">You can log in to your account using:<br>
          <strong>Email:</strong> {{email}}
        </p>
        <hr style="border:none; border-top:1px solid #eeeeee; margin:20px 0;">
        <p style="color:#aaaaaa; font-size:12px;">If you did not create this account, please ignore this email.</p>
      </td>
    </tr>
  </table>
</body>
</html>',
                'status'  => 1,
            ],
            [
    'name'    => 'user_2fa_code',
    'subject' => 'Your Verification Code - ' . $appName,
    'body'    => '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
  <table width="600" style="margin:0 auto; background:#ffffff; padding:30px; border-radius:8px;">
    <tr>
      <td>
        <h2 style="color:#333333;">Hello, {{name}}!</h2>
        <p style="color:#555555;">Use the following verification code to complete your sign in to <strong>' . $appName . '</strong>:</p>

        <p style="text-align:center; margin:30px 0;">
          <span style="display:inline-block; font-size:36px; font-weight:bold; letter-spacing:10px; color:#4F46E5; background:#F3F4FF; padding:16px 32px; border-radius:8px;">
            {{otp}}
          </span>
        </p>

        <p style="color:#555555;">This code will expire in <strong>' . config('constants.OTP_EXPIRY_MINUTES') . ' minutes</strong>.</p>

        <hr style="border:none; border-top:1px solid #eeeeee; margin:20px 0;">

        <p style="color:#aaaaaa; font-size:12px;">
          If you did not request this code, please ignore this email and secure your account.
        </p>
      </td>
    </tr>
  </table>
</body>
</html>',
    'status'  => 1,
],
[
    'name'    => 'user_reset_password',
    'subject' => 'Reset Your Password - ' . $appName,
    'body'    => '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
  <table width="600" style="margin:0 auto; background:#ffffff; padding:30px; border-radius:8px;">
    <tr>
      <td>
        <h2 style="color:#333333;">Hello, {{name}}!</h2>

        <p style="color:#555555;">
          We received a request to reset the password for your <strong>' . $appName . '</strong> account.
        </p>

        <p style="color:#555555;">
          Click the button below to create a new password.
        </p>

        <p style="text-align:center; margin:30px 0;">
          <a href="{{reset_link}}"
             style="background:#4F46E5; color:#ffffff; padding:12px 28px; border-radius:5px; text-decoration:none; font-size:15px;">
            Reset Password
          </a>
        </p>

        <p style="color:#555555;">
          This password reset link will expire in <strong>60 minutes</strong>.
        </p>

        <p style="color:#555555;">
          If you did not request a password reset, you can safely ignore this email.
        </p>

        <hr style="border:none; border-top:1px solid #eeeeee; margin:20px 0;">

        <p style="color:#aaaaaa; font-size:12px;">
          If the button above does not work, copy and paste the following URL into your browser:
        </p>

        <p style="word-break:break-all;">
          <a href="{{reset_link}}" style="color:#4F46E5;">
            {{reset_link}}
          </a>
        </p>
      </td>
    </tr>
  </table>
</body>
</html>',
    'status'  => 1,
],
[
    'name'    => 'project_assigned',
    'subject' => 'New Project Assigned - ' . $appName,
    'body'    => '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
  <table width="600" style="margin:0 auto; background:#ffffff; padding:30px; border-radius:8px;">
    <tr>
      <td>
        <h2 style="color:#333333;">Hello, {{name}}!</h2>

        <p style="color:#555555;">
          A new project has been assigned to you in <strong>' . $appName . '</strong>.
        </p>

        <table style="width:100%; border-collapse:collapse; margin:20px 0;">
          <tr>
            <td style="padding:10px; border:1px solid #e5e5e5;"><strong>Project Name</strong></td>
            <td style="padding:10px; border:1px solid #e5e5e5;">{{project_name}}</td>
          </tr>
          <tr>
            <td style="padding:10px; border:1px solid #e5e5e5;"><strong>Assigned By</strong></td>
            <td style="padding:10px; border:1px solid #e5e5e5;">{{assigned_by}}</td>
          </tr>
          <tr>
            <td style="padding:10px; border:1px solid #e5e5e5;"><strong>Assigned On</strong></td>
            <td style="padding:10px; border:1px solid #e5e5e5;">{{assigned_date}}</td>
          </tr>
        </table>

        <p style="color:#555555;">
          Please log in to the system and review the project details and assigned tasks.
        </p>

        <p style="text-align:center; margin:30px 0;">
          <a href="{{project_url}}"
             style="background:#4F46E5; color:#ffffff; padding:12px 28px; border-radius:5px; text-decoration:none; font-size:15px;">
            View Project
          </a>
        </p>

        <hr style="border:none; border-top:1px solid #eeeeee; margin:20px 0;">

        <p style="color:#aaaaaa; font-size:12px;">
          This is an automated notification from ' . $appName . '.
        </p>
      </td>
    </tr>
  </table>
</body>
</html>',
    'status'  => 1,
],
            [
                'name'    => 'all_items_verified',
                'subject' => 'All Checklist Items Verified – {{project_name}}',
                'body'    => '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
  <table width="600" style="margin:0 auto; background:#ffffff; padding:30px; border-radius:8px;">
    <tr>
      <td>
        <h2 style="color:#333333;">Hello, {{admin_name}}!</h2>

        <p style="color:#555555;">
          All checklist items for the project <strong>{{project_name}}</strong> have been verified.
        </p>

        <table style="width:100%; border-collapse:collapse; margin:20px 0;">
          <tr>
            <td style="padding:10px; border:1px solid #e5e5e5;"><strong>Project</strong></td>
            <td style="padding:10px; border:1px solid #e5e5e5;">{{project_name}}</td>
          </tr>
          <tr>
            <td style="padding:10px; border:1px solid #e5e5e5;"><strong>Verified By</strong></td>
            <td style="padding:10px; border:1px solid #e5e5e5;">{{verifier_name}}</td>
          </tr>
          <tr>
            <td style="padding:10px; border:1px solid #e5e5e5;"><strong>Completed On</strong></td>
            <td style="padding:10px; border:1px solid #e5e5e5;">{{verified_at}}</td>
          </tr>
        </table>

        <p style="text-align:center; margin:30px 0;">
          <a href="{{project_url}}"
             style="background:#4F46E5; color:#ffffff; padding:12px 28px; border-radius:5px; text-decoration:none; font-size:15px;">
            View Project
          </a>
        </p>

        <hr style="border:none; border-top:1px solid #eeeeee; margin:20px 0;">
        <p style="color:#aaaaaa; font-size:12px;">This is an automated notification from ' . $appName . '.</p>
      </td>
    </tr>
  </table>
</body>
</html>',
                'status'  => 1,
            ],
        ];

        foreach ($templates as $template) {
            DB::table(config('tables.email_templates'))
                ->updateOrInsert(['name' => $template['name']], $template);
        }
    }
}
