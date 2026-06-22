<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $siteSetting?->site_name ?? 'Admin' }} : @yield('admin-title')</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- TOASTR --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">

    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ ($siteSetting?->site_logo) ? asset($siteSetting->site_logo) : asset('assets/images/fav-icon.png') }}">
    
    <!-- Datatables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="{{ asset('assets/css/datatable.css') }}" rel="stylesheet">
    <!-- Cropper.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    {{-- Dynamic theme colours from Site Settings --}}
    @php
        $primaryColour   = $siteSetting?->admin_theme_colour     ?? '#2563eb';
        $secondaryColour = $siteSetting?->admin_secondary_colour  ?? '#64748b';
    @endphp
    <style>
        :root {
            --bs-primary:        {{ $primaryColour }};
            --bs-primary-rgb:    {{ implode(',', sscanf($primaryColour, '#%02x%02x%02x')) }};
            --bs-secondary:      {{ $secondaryColour }};
            --bs-secondary-rgb:  {{ implode(',', sscanf($secondaryColour, '#%02x%02x%02x')) }};
            --theme-primary:     {{ $primaryColour }};
            --theme-secondary:   {{ $secondaryColour }};

            /* Override style.css variables so ALL existing buttons / sidebar / toggles update */
            --secondarycolor:    {{ $secondaryColour }};
            --primarycolor:      {{ $primaryColour }};
        }

        /* Prevent active li's primary background bleeding into submenu item anchors */
        .menubar li.active .submenu li a {
            background: transparent;
        }

    </style>

    @stack('styles')

</head>

<body class="innerbody">

    @if (session('flash-error'))
        <span class="admin-toastr" onclick="toastr_alert('Error','{{ session()->get('flash-error') }}','error')"></span>
    @endif
    @if (session('flash-success'))
        <span class="admin-toastr"
            onclick="toastr_alert('Success','{{ session()->get('flash-success') }}','success')"></span>
    @endif
    @php $authRole = (int) (auth('admin')->user()?->role ?? 0); @endphp
    <aside class="sidebar">
        <div class="closemenu-btn"><img src="{{ asset('assets/images/Close_round_fill.png') }}" class="img-fluid"></div>
        <a href="{{ $authRole === 1 ? route('admin.dashboard') : route('user.dashboard') }}">
        <div class="text-center sildebarlogo">
            @php $logoSrc = ($siteSetting?->site_logo) ? asset($siteSetting->site_logo) : asset('assets/images/sidebarlogo.svg'); @endphp
            <img src="{{ $logoSrc }}" class="img-fluid logo-full" alt="{{ $siteSetting?->site_name ?? 'Admin' }}" style="max-height:60px; object-fit:contain;">
            <img src="{{ $logoSrc }}" class="img-fluid logo-icon" alt="logo" style="max-height:40px; object-fit:contain;">
        </div>
        </a>
        <div class="menubar-holder">
            
            
            <ul class="menubar">

                @if($authRole === 1)
                {{-- ── Admin menu ─────────────────────────────────────────────── --}}
                <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <span class="menu-icon"><i class="fa fa-gauge-high"></i></span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="mt-2 {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.userIndex') }}">
                        <span class="menu-icon"><i class="fa fa-users"></i></span>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="mt-2 {{ request()->routeIs('admin.siteSetting.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.siteSetting.index') }}">
                        <span class="menu-icon"><i class="fa fa-gear"></i></span>
                        <span>Site Settings</span>
                    </a>
                </li>
               
                {{-- <li class="mt-2 {{ request()->routeIs('admin.tech-stack.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.tech-stack.index') }}">
                        <span class="menu-icon"><i class="fa fa-layer-group"></i></span>
                        <span>Tech Stack Master</span>
                    </a>
                </li> --}}
                <li class="mt-2 {{ request()->routeIs('admin.project.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.project.index') }}">
                        <span class="menu-icon"><i class="fa fa-diagram-project"></i></span>
                        <span>Projects</span>
                    </a>
                </li>
                <li class="mt-2 {{ request()->routeIs('admin.project-reports.*') || request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <a href="javascript:void(0)" class="has-submenu {{ request()->routeIs('admin.project-reports.*') || request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="fa fa-chart-bar"></i></span>
                        <span>Reports</span>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="{{ route('admin.project-reports.index') }}" class="{{ request()->routeIs('admin.project-reports.*') ? 'active' : '' }}">
                                Assessment-wise Detail Report
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.assessment-list') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                                Assessment List Report
                            </a>
                        </li>
                    </ul>
                </li>
                
                @elseif($authRole === 2)
                {{-- ── Verification User menu ───────────────────────────────────── --}}
                <li class="mt-2 {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('user.dashboard') }}">
                        <span class="menu-icon"><i class="fa fa-gauge"></i></span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="mt-2 {{ request()->routeIs('user.project.*') ? 'active' : '' }}">
                    <a href="{{ route('user.project.index') }}">
                        <span class="menu-icon"><i class="fa fa-diagram-project"></i></span>
                        <span>My Assigned Projects</span>
                    </a>
                </li>
                <li class="mt-2 {{ request()->routeIs('user.reports.*') ? 'active' : '' }}">
                    <a href="{{ enroute('user.reports.index') }}">
                        <span class="menu-icon"><i class="fa fa-chart-bar"></i></span>
                        <span>My Project Reports</span>
                    </a>
                </li>
                @endif

            </ul>

        </div>
    </aside>
    <!--header part-->
    <div class="header">
        <a href="javascript:void(0)" class="slidetoggle"><img src="{{ asset('assets/images/menu (2).svg') }}"> </a>
        <div class="user-set-menu dropdown">
            <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                <img src="{{ $currentUserInfo->profile_photo ? asset($currentUserInfo->profile_photo) : asset('assets/images/user-lg-pic.svg') }}" class="me-2 rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                <div style="line-height: 1;" class="pe-2">
                    <h6>{{ ucfirst($currentUserInfo->first_name) }}</h6>
                </div>
            </a>
            <ul class="dropdown-menu user-menu-dropdown">
                <li>
                    <a class="dropdown-item" href="{{ $authRole === 1 ? route('admin.getProfile') : route('user.getProfile') }}">
                        <span class="menu-icon"><i class="fa fa-user"></i></span>
                        <span>Profile</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ $authRole === 1 ? route('admin.changePassword.view') : route('user.changePassword.view')  }}">
                        <span class="menu-icon"><i class="fa fa-lock"></i></span>
                        <span>Change Password</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item dropdown-item-logout" href="{{ $authRole === 1 ? route('admin.logout') : route('user.logout') }}">
                        <span class="menu-icon"><i class="fa fa-arrow-right-from-bracket"></i></span>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!--header part-->
    {{-- ── Global Image Cropper Modal ─────────────────────────────────── --}}
    <div class="modal fade" id="cropperModal" tabindex="-1" aria-labelledby="cropperModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cropperModalLabel">
                        <i class="fa fa-crop-alt me-2" style="color:var(--secondarycolor);"></i>Crop Image
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Cropper toolbar: zoom, rotate, flip, aspect ratio, reset --}}
                <div class="cropper-toolbar" id="cropperToolbar" role="toolbar" aria-label="Image edit tools">
                    <div class="cropper-toolbar-group" role="group" aria-label="Zoom">
                        <button type="button" class="cropper-tool" data-cropper-action="zoomIn" title="Zoom in" aria-label="Zoom in">
                            <i class="fa fa-search-plus"></i>
                        </button>
                        <button type="button" class="cropper-tool" data-cropper-action="zoomOut" title="Zoom out" aria-label="Zoom out">
                            <i class="fa fa-search-minus"></i>
                        </button>
                    </div>
                    <div class="cropper-toolbar-group" role="group" aria-label="Rotate">
                        <button type="button" class="cropper-tool" data-cropper-action="rotateLeft" title="Rotate 90° left" aria-label="Rotate left">
                            <i class="fa fa-undo"></i>
                        </button>
                        <button type="button" class="cropper-tool" data-cropper-action="rotateRight" title="Rotate 90° right" aria-label="Rotate right">
                            <i class="fa fa-redo"></i>
                        </button>
                    </div>
                    <div class="cropper-toolbar-group" role="group" aria-label="Flip">
                        <button type="button" class="cropper-tool" data-cropper-action="flipHorizontal" title="Flip horizontal" aria-pressed="false" aria-label="Flip horizontal">
                            <i class="fa fa-arrows-alt-h"></i>
                        </button>
                        <button type="button" class="cropper-tool" data-cropper-action="flipVertical" title="Flip vertical" aria-pressed="false" aria-label="Flip vertical">
                            <i class="fa fa-arrows-alt-v"></i>
                        </button>
                    </div>
                    <div class="cropper-toolbar-group cropper-aspect-group" role="group" aria-label="Aspect ratio">
                        <span class="cropper-toolbar-label">Ratio</span>
                        <button type="button" class="cropper-tool cropper-aspect" data-cropper-aspect="free" title="Free aspect ratio">
                            <span class="cropper-tool-label">Free</span>
                        </button>
                        <button type="button" class="cropper-tool cropper-aspect" data-cropper-aspect="1" title="Square 1:1">
                            <span class="cropper-tool-label">1:1</span>
                        </button>
                        <button type="button" class="cropper-tool cropper-aspect" data-cropper-aspect="4/3" title="4:3">
                            <span class="cropper-tool-label">4:3</span>
                        </button>
                        <button type="button" class="cropper-tool cropper-aspect" data-cropper-aspect="16/9" title="16:9">
                            <span class="cropper-tool-label">16:9</span>
                        </button>
                        <button type="button" class="cropper-tool cropper-aspect" data-cropper-aspect="3/4" title="Portrait 3:4">
                            <span class="cropper-tool-label">3:4</span>
                        </button>
                    </div>
                    <div class="cropper-toolbar-group ms-auto" role="group" aria-label="Reset">
                        <button type="button" class="cropper-tool" data-cropper-action="reset" title="Reset all changes">
                            <i class="fa fa-sync-alt me-1"></i>
                            <span class="cropper-tool-label">Reset</span>
                        </button>
                    </div>
                </div>

                <div class="modal-body text-center">
                    <img id="cropperModalImage" src="" alt="Crop preview">
                </div>
                <div class="modal-footer justify-content-between">
                    <small class="text-muted">
                        <i class="fa fa-info-circle me-1"></i>
                        Scroll to zoom &nbsp;&middot;&nbsp; Drag to move &nbsp;&middot;&nbsp; Resize handles to adjust
                    </small>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="cropperConfirmBtn">
                            Crop &amp; Use
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- ── /Global Image Cropper Modal ────────────────────────────────── --}}

    <!--PAGE CONTENT-->
    @yield('content')
    <!--PAGE CONTENT-->
    <script>
        var csrf = "{{ csrf_token() }}";
        var baseUrl = "{{ url('/') }}";
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

    {{-- TOASTR --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Datatables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>


    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <!-- Cropper.js -->
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
    <script src="{{ asset('assets/js/cropper-init.js') }}"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(function () {
         $('select.select2:not([data-no-select2])').select2({
            width: '100%'
        });
        $('select[multiple]').not('[data-no-select2]').each(function () {
            var $el = $(this);
            $el.select2({
                theme:       'bootstrap-5',
                width:       '100%',
                allowClear:  true,
                placeholder: $el.data('placeholder') || 'Select options...',
            });
        });
       
    });
    </script>
    @stack('scripts')
</body>

</html>
