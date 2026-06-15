@extends('admin.layout.index')
@section('admin-title', 'Site Settings')

@section('content')
<div class="container-fluid pt-3">
    <div class="page-content-wrapper">

        <div class="page-title-row">
            <div class="page-title-row-text">
                <h4>Site Settings</h4>
                <p class="page-title-row-sub">Branding, identity, and global preferences for your admin panel.</p>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="whiteBg settings-shell">
                    <form action="{{ route('admin.siteSetting.update') }}" method="POST" enctype="multipart/form-data" id="siteSettingForm">
                        @csrf
                        @method('PUT')

                        {{-- ── Tab navigation ─────────────────────────── --}}
                        <ul class="nav nav-tabs settings-tabs" id="settingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-general-btn"
                                        data-bs-toggle="tab" data-bs-target="#tab-general"
                                        type="button" role="tab"
                                        aria-controls="tab-general" aria-selected="true">
                                    <i class="fa fa-sliders"></i>
                                    <span>General</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-branding-btn"
                                        data-bs-toggle="tab" data-bs-target="#tab-branding"
                                        type="button" role="tab"
                                        aria-controls="tab-branding" aria-selected="false">
                                    <i class="fa fa-palette"></i>
                                    <span>Branding</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-auth-btn"
                                        data-bs-toggle="tab" data-bs-target="#tab-auth"
                                        type="button" role="tab"
                                        aria-controls="tab-auth" aria-selected="false">
                                    <i class="fa fa-key"></i>
                                    <span>Auth Pages</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-security-btn"
                                        data-bs-toggle="tab" data-bs-target="#tab-security"
                                        type="button" role="tab"
                                        aria-controls="tab-security" aria-selected="false">
                                    <i class="fa fa-shield-halved"></i>
                                    <span>Security</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-social-btn"
                                        data-bs-toggle="tab" data-bs-target="#tab-social"
                                        type="button" role="tab"
                                        aria-controls="tab-social" aria-selected="false">
                                    <i class="fa fa-share-nodes"></i>
                                    <span>Social</span>
                                </button>
                            </li>                           
                        </ul>

                        {{-- ── Tab content ────────────────────────────── --}}
                        <div class="tab-content settings-tab-content" id="settingsTabsContent">

                            {{-- ─────────── GENERAL TAB ─────────── --}}
                            <div class="tab-pane fade show active"
                                 id="tab-general" role="tabpanel"
                                 aria-labelledby="tab-general-btn">

                                <div class="settings-pane">
                                    <div class="settings-pane-header">
                                        <h5 class="settings-pane-title">General</h5>
                                        <p class="settings-pane-sub">Identity of the platform — site name and logo shown across the admin panel.</p>
                                    </div>

                                    <div class="row">
                                        {{-- Site Name --}}
                                        <div class="col-md-6 mb-3">
                                            <label for="site_name" class="form-label">
                                                Site Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                class="form-control no-leading-space @error('site_name') is-invalid @enderror"
                                                name="site_name" id="site_name"
                                                value="{{ old('site_name', $setting->site_name) }}"
                                                placeholder="Enter site name" maxlength="150">
                                            @error('site_name')
                                                <div class="form-valid-error">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Site Logo --}}
                                        <div class="col-md-6 mb-3">
                                            <label for="site_logo" class="form-label">Site Logo</label>
                                            <div class="d-flex align-items-start gap-3">
                                                <div id="logo-preview-wrapper" class="upload-preview-box">
                                                    @if($setting->site_logo)
                                                        <img id="logo-preview"
                                                            src="{{ asset($setting->site_logo) }}"
                                                            alt="Logo">
                                                    @else
                                                        <img id="logo-preview" src="" alt="Logo" style="display:none;">
                                                        <i class="fa fa-image text-muted" id="logo-placeholder-icon"></i>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <input type="file"
                                                        class="form-control @error('site_logo') is-invalid @enderror"
                                                        name="site_logo" id="site_logo"
                                                        accept=".jpg,.jpeg,.png,.svg,.webp"
                                                        data-preview="#logo-preview"
                                                        data-placeholder-icon="#logo-placeholder-icon"
                                                        data-crop-ratio="1"
                                                        data-crop-width="400"
                                                        data-crop-height="400"
                                                        data-min-crop-width="180"
                                                        data-min-crop-height="180"
                                                        >
                                                        <small class="text-muted d-block mt-1">
                                                            Recommended: <strong>400×400</strong> (square), max <strong>2 MB</strong>. Formats: <strong>JPG, PNG, WebP</strong>.
                                                        </small>

                                                    @error('site_logo')
                                                        <div class="form-valid-error">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ─────────── BRANDING TAB ─────────── --}}
                            <div class="tab-pane fade"
                                 id="tab-branding" role="tabpanel"
                                 aria-labelledby="tab-branding-btn">

                                <div class="settings-pane">
                                    <div class="settings-pane-header">
                                        <h5 class="settings-pane-title">Theme Colours</h5>
                                        <p class="settings-pane-sub">Brand colours applied across buttons, links, and accents in the admin theme.</p>
                                    </div>

                                    <div class="row">
                                        {{-- Primary Colour --}}
                                        <div class="col-md-6 mb-3">
                                            <label for="admin_theme_colour" class="form-label">
                                                Primary Colour <span class="text-danger">*</span>
                                            </label>
                                            <div class="colour-picker-row">
                                                <input type="color"
                                                    name="admin_theme_colour" id="admin_theme_colour"
                                                    value="{{ old('admin_theme_colour', $setting->admin_theme_colour ?? '#2563eb') }}">
                                                <input type="text"
                                                    class="form-control @error('admin_theme_colour') is-invalid @enderror"
                                                    id="admin_theme_colour_text"
                                                    value="{{ old('admin_theme_colour', $setting->admin_theme_colour ?? '#2563eb') }}"
                                                    placeholder="#2563eb" maxlength="7">
                                            </div>
                                            @error('admin_theme_colour')
                                                <div class="form-valid-error">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Secondary Colour --}}
                                        <div class="col-md-6 mb-3">
                                            <label for="admin_secondary_colour" class="form-label">
                                                Secondary Colour <span class="text-danger">*</span>
                                            </label>
                                            <div class="colour-picker-row">
                                                <input type="color"
                                                    name="admin_secondary_colour" id="admin_secondary_colour"
                                                    value="{{ old('admin_secondary_colour', $setting->admin_secondary_colour ?? '#64748b') }}">
                                                <input type="text"
                                                    class="form-control @error('admin_secondary_colour') is-invalid @enderror"
                                                    id="admin_secondary_colour_text"
                                                    value="{{ old('admin_secondary_colour', $setting->admin_secondary_colour ?? '#64748b') }}"
                                                    placeholder="#64748b" maxlength="7">
                                            </div>
                                            @error('admin_secondary_colour')
                                                <div class="form-valid-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ─────────── AUTH PAGES TAB ─────────── --}}
                            <div class="tab-pane fade"
                                 id="tab-auth" role="tabpanel"
                                 aria-labelledby="tab-auth-btn">

                                <div class="settings-pane">
                                    <div class="settings-pane-header">
                                        <h5 class="settings-pane-title">Auth Page Side Banner</h5>
                                        <p class="settings-pane-sub">Visual shown beside the sign-in / forgot-password forms.</p>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="auth_side_banner" class="form-label">Banner Image</label>
                                            <div class="banner-uploader">
                                                <div id="auth-banner-preview-wrapper" class="banner-preview">
                                                    @if(!empty($setting->auth_side_banner))
                                                        <img id="auth-banner-preview"
                                                            src="{{ asset($setting->auth_side_banner) }}"
                                                            alt="Auth Banner">
                                                    @else
                                                        <img id="auth-banner-preview" src="" alt="Auth Banner" style="display:none;">
                                                        <div class="banner-placeholder" id="auth-banner-placeholder-icon">
                                                            <i class="fa fa-image"></i>
                                                            <span>No banner uploaded</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="banner-controls">
                                                    <input type="file"
                                                        class="form-control @error('auth_side_banner') is-invalid @enderror"
                                                        name="auth_side_banner" id="auth_side_banner"
                                                        accept=".jpg,.jpeg,.png,.webp"
                                                        data-preview="#auth-banner-preview"
                                                        data-placeholder-icon="#auth-banner-placeholder-icon"
                                                        data-crop-ratio="0.8"
                                                        data-crop-width="800"
                                                        data-crop-height="1000"
                                                        data-min-crop-width="240"
                                                        data-min-crop-height="300"
                                                        >
                                                    <small class="text-muted d-block mt-2">
                                                        Shown on the left half of <strong>login</strong>, <strong>forgot password</strong>,
                                                        and <strong>reset password</strong> screens.Recommended: <strong>800X1000</strong> (square), max <strong>2 MB</strong>. Formats: <strong>JPG, PNG, WebP</strong>.
                                                    </small>                                                    
                                                    @error('auth_side_banner')
                                                        <div class="form-valid-error">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ─────────── SECURITY TAB ─────────── --}}
                            <div class="tab-pane fade"
                                 id="tab-security" role="tabpanel"
                                 aria-labelledby="tab-security-btn">

                                <div class="settings-pane">
                                    <div class="settings-pane-header">
                                        <h5 class="settings-pane-title">Security</h5>
                                        <p class="settings-pane-sub">Account-protection controls applied to every admin sign-in.</p>
                                    </div>

                                    {{-- Two-factor authentication toggle --}}
                                    <div class="setting-toggle-card">
                                        <div class="stc-leading">
                                            <div class="stc-icon">
                                                <i class="fa fa-shield-halved"></i>
                                            </div>
                                            <div class="stc-meta">
                                                <h6 class="stc-title">
                                                    Two-Factor Authentication (2FA)
                                                    {{-- Two state badges: which one is visible is
                                                         driven entirely by the toggle's `:checked`
                                                         state via `:has()` in custom.css. --}}
                                                    <span class="stc-state stc-state-on">Enabled</span>
                                                    <span class="stc-state stc-state-off">Disabled</span>
                                                </h6>
                                                <p class="stc-desc">
                                                    Require a one-time passcode in addition to the password during sign-in.
                                                    Highly recommended for accounts that manage user data.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="stc-trailing">
                                            {{-- Hidden field guarantees a value of 0 even when the
                                                 toggle is off (HTML doesn't submit unchecked checkboxes).
                                                 The visible checkbox below uses the same name and
                                                 simply overrides it when checked. --}}
                                            <input type="hidden" name="two_factor_enabled" value="0">
                                            <label class="switch setting-toggle">
                                                <input type="checkbox"
                                                       name="two_factor_enabled"
                                                       id="two_factor_enabled"
                                                       value="1"
                                                       {{ !empty($setting->two_factor_enabled) ? 'checked' : '' }}>
                                                <span class="slider-table"><span class="slider-table-text"></span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ─────────── SOCIAL TAB ─────────── --}}
                            <div class="tab-pane fade"
                                 id="tab-social" role="tabpanel"
                                 aria-labelledby="tab-social-btn">

                                <div class="settings-pane">
                                    <div class="settings-pane-header">
                                        <h5 class="settings-pane-title">Social Login URLs</h5>
                                        <p class="settings-pane-sub">Public profile links rendered as social icons in the front-end footer.</p>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="facebook_url" class="form-label">
                                                Facebook URL
                                            </label>
                                            <input type="url"
                                                class="form-control no-space @error('facebook_url') is-invalid @enderror"
                                                name="facebook_url" id="facebook_url"
                                                value="{{ old('facebook_url', $setting->facebook_url) }}"
                                                placeholder="https://facebook.com/yourpage">
                                            @error('facebook_url')
                                                <div class="form-valid-error">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="instagram_url" class="form-label">
                                                Instagram URL
                                            </label>
                                            <input type="url"
                                                class="form-control no-space @error('instagram_url') is-invalid @enderror"
                                                name="instagram_url" id="instagram_url"
                                                value="{{ old('instagram_url', $setting->instagram_url) }}"
                                                placeholder="https://instagram.com/yourprofile">
                                            @error('instagram_url')
                                                <div class="form-valid-error">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="linkedin_url" class="form-label">
                                                LinkedIn URL
                                            </label>
                                            <input type="url"
                                                class="form-control no-space @error('linkedin_url') is-invalid @enderror"
                                                name="linkedin_url" id="linkedin_url"
                                                value="{{ old('linkedin_url', $setting->linkedin_url) }}"
                                                placeholder="https://linkedin.com/in/yourprofile">
                                            @error('linkedin_url')
                                                <div class="form-valid-error">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="twitter_url" class="form-label">
                                                Twitter / X URL
                                            </label>
                                            <input type="url"
                                                class="form-control no-space @error('twitter_url') is-invalid @enderror"
                                                name="twitter_url" id="twitter_url"
                                                value="{{ old('twitter_url', $setting->twitter_url) }}"
                                                placeholder="https://twitter.com/yourhandle">
                                            @error('twitter_url')
                                                <div class="form-valid-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Save (sticky-feeling footer, single submit for ALL tabs) --}}
                        <div class="settings-footer row align-items-center">

                            <!-- Left Side Text -->
                            <div class="col-md-6 d-flex align-items-center">
                                <small class="text-muted">
                                    All tabs are saved with one click.
                                </small>
                            </div>

                            <!-- Right Side Buttons -->
                            <div class="col-md-6 d-flex justify-content-md-end justify-content-start gap-2 mt-3 mt-md-0">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">Save</button>
                            </div>

                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Primary colour picker
    const primaryPicker = document.getElementById('admin_theme_colour');
    const primaryText   = document.getElementById('admin_theme_colour_text');
    primaryPicker.addEventListener('input', () => primaryText.value = primaryPicker.value);
    primaryText.addEventListener('input', function () {
        if (/^#[0-9A-Fa-f]{3,6}$/.test(this.value)) primaryPicker.value = this.value;
    });

    // Secondary colour picker
    const secondaryPicker = document.getElementById('admin_secondary_colour');
    const secondaryText   = document.getElementById('admin_secondary_colour_text');
    secondaryPicker.addEventListener('input', () => secondaryText.value = secondaryPicker.value);
    secondaryText.addEventListener('input', function () {
        if (/^#[0-9A-Fa-f]{3,6}$/.test(this.value)) secondaryPicker.value = this.value;
    });

    // Logo + auth banner live previews are handled by the global cropper-init.js
</script>
@endpush
