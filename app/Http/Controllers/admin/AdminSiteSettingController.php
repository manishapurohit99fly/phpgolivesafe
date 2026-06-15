<?php

declare(strict_types=1);

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\Menu;
use App\Services\SiteSettingService;
use Illuminate\Http\Request;

class AdminSiteSettingController extends Controller
{
    public function __construct(private readonly SiteSettingService $siteSettingService) {}
    public function index()
    {
        $setting = $this->siteSettingService->getSettings();
        $pages = CmsPage::pluck('title', 'slug'); 
        $menus = Menu::pluck('name','slug', 'id'); // Assuming you have a Menu model for header menu options
        return view('admin.site-setting.index', compact('setting','pages', 'menus'));
    }

    public function update(Request $request)
    {
        $fileKeys = ['site_logo', 'auth_side_banner', 'web_site_logo', 'web_favicon'];

        // Files keyed by their setting_key. Adding a new image setting
        // only needs a new entry here AND in SiteSettingService::FILE_FIELDS.
        $files = [];
        foreach ($fileKeys as $key) {
            $files[$key] = $request->file($key);
        }

        // Collect all scalar form fields, excluding Laravel internals and file fields.
        $data = $request->except(array_merge(['_token', '_method'], $fileKeys));

        // The 2FA toggle ships hidden=0 + checkbox=1 so a value is always
        // submitted. Normalise to a strict '0' / '1' string.
        $data['two_factor_enabled'] = $request->boolean('two_factor_enabled') ? '1' : '0';

        $this->siteSettingService->updateSettings($data, $files);

        return redirect()
            ->route('admin.siteSetting.index')
            ->with('flash-success', __('messages.site_settings_updated'));
    }

    /**
     * Validation rules for every field on every tab of the form.
     *
     * @return array<string, array<int, string>>
     */
    private function rules(): array
    {
        return [
            // ── Admin: General ─────────────────────────────────────────
            'site_name'              => ['required', 'string', 'max:150'],
            'site_logo'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],

            // ── Admin: Branding ────────────────────────────────────────
            'admin_theme_colour'     => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{3,6}$/'],
            'admin_secondary_colour' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{3,6}$/'],

            // ── Admin: Auth pages ──────────────────────────────────────
            'auth_side_banner'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // ── Admin: Security ────────────────────────────────────────
            'two_factor_enabled'     => ['nullable', 'boolean'],

            // ── Admin: Social ──────────────────────────────────────────
            'facebook_url'           => ['nullable', 'url', 'max:500'],
            'instagram_url'          => ['nullable', 'url', 'max:500'],
            'linkedin_url'           => ['nullable', 'url', 'max:500'],
            'twitter_url'            => ['nullable', 'url', 'max:500'],

            // ── Website: General ───────────────────────────────────────
            'web_site_name'          => ['nullable', 'string', 'max:150'],
            'web_site_email'         => ['nullable', 'email', 'max:150'],
            'web_site_logo'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'web_favicon'            => ['nullable', 'image', 'mimes:ico,png,svg,webp,jpg,jpeg', 'max:1024'],
            'web_website_status'     => ['nullable', 'in:live,maintenance,coming_soon'],
            'web_timezone'           => ['nullable', 'string', 'max:100'],
            'web_front_page'         => ['nullable', 'string', 'max:100'],

            // ── Website: Header ────────────────────────────────────────
            'web_header_layout'      => ['nullable', 'in:normal,transparent'],
            'web_header_position'    => ['nullable', 'in:fixed,sticky,static'],
            'web_header_menu'        => ['required', 'string', 'max:150'],
            
            // ── Website: Footer ────────────────────────────────────────
            'web_footer_copyright_1' => ['nullable', 'string', 'max:255'],
            'web_footer_copyright_2' => ['nullable', 'string', 'max:255'],
            'web_footer_about_title' => ['nullable', 'string', 'max:500'],

            // ── Website: Social ────────────────────────────────────────
            'web_facebook_url'       => ['nullable', 'url', 'max:150'],
            'web_instagram_url'      => ['nullable', 'url', 'max:150'],
            'web_linkedin_url'       => ['nullable', 'url', 'max:150'],
            'web_twitter_url'        => ['nullable', 'url', 'max:150'],

            // ── Website: SMTP ──────────────────────────────────────────
            'web_smtp_host'          => ['nullable', 'string', 'max:150'],
            'web_smtp_port'          => ['nullable', 'string', 'max:10'],
            'web_smtp_username'      => ['nullable', 'string', 'max:150'],
            'web_smtp_password'      => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * Translated messages for the admin-tab fields. Website-tab fields
     * fall back to Laravel's defaults to keep the lang file lean.
     *
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'site_name.required'              => __('messages.site_name_required'),
            'site_name.max'                   => __('messages.site_name_max'),
            'site_logo.image'                 => __('messages.site_logo_image'),
            'site_logo.mimes'                 => __('messages.site_logo_mimes'),
            'site_logo.max'                   => __('messages.site_logo_max'),
            'auth_side_banner.image'          => __('messages.auth_side_banner_image'),
            'auth_side_banner.mimes'          => __('messages.auth_side_banner_mimes'),
            'auth_side_banner.max'            => __('messages.auth_side_banner_max'),
            'admin_theme_colour.required'     => __('messages.admin_theme_colour_required'),
            'admin_theme_colour.regex'        => __('messages.admin_theme_colour_regex'),
            'admin_secondary_colour.required' => __('messages.admin_secondary_colour_required'),
            'admin_secondary_colour.regex'    => __('messages.admin_secondary_colour_regex'),
            'facebook_url.url'                => __('messages.facebook_url_url'),
            'instagram_url.url'               => __('messages.instagram_url_url'),
            'linkedin_url.url'                => __('messages.linkedin_url_url'),
            'twitter_url.url'                 => __('messages.twitter_url_url'),
            'web_site_name.max'              => __('messages.web_site_name_max'),
            'web_site_email.email'           => __('messages.web_site_email_email'),
            'web_site_email.max'             => __('messages.web_site_email_max'),
            'web_site_logo.image'           => __('messages.web_site_logo_image'),
            'web_site_logo.mimes'           => __('messages.web_site_logo_mimes'),
            'web_site_logo.max'             => __('messages.web_site_logo_max'),
            'web_favicon.image'             => __('messages.web_favicon_image'),
            'web_favicon.mimes'             => __('messages.web_favicon_mimes'),
            'web_favicon.max'               => __('messages.web_favicon_max'),
            'web_website_status.in'         => __('messages.web_website_status_in'),
            'web_timezone.max'              => __('messages.web_timezone_max'),
            'web_front_page.max'            => __('messages.web_front_page_max'),       
            'web_header_layout.in'         => __('messages.web_header_layout_in'),
            'web_header_position.in'       => __('messages.web_header_position_in'),
            'web_footer_copyright_1.max'  => __('messages.web_footer_copyright_1_max'),
            'web_footer_copyright_2.max'  => __('messages.web_footer_copyright_2_max'),
            'web_footer_about_title.max'  => __('messages.web_footer_about_title_max'),
            'web_facebook_url.url'        => __('messages.web_facebook_url_url'),
            'web_instagram_url.url'       => __('messages.web_instagram_url_url'),
            'web_linkedin_url.url'       => __('messages.web_linkedin_url_url'),
            'web_twitter_url.url'        => __('messages.web_twitter_url_url'),
            'web_smtp_host.max'          => __('messages.web_smtp_host_max'),
            'web_smtp_port.max'          => __('messages.web_smtp_port_max'),
            'web_smtp_username.max'      => __('messages.web_smtp_username_max'),
            'web_smtp_password.max'      => __('messages.web_smtp_password_max'),
            'web_header_menu.required'      => __('messages.web_header_menu_required'),
        ];
    }
}
