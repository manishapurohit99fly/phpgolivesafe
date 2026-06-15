<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} @yield('admin-title')</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ ($siteSetting?->site_logo) ? asset($siteSetting->site_logo) : asset('assets/images/fav-icon.png') }}">

    {{-- TOASTR --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
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
    </style>

</head>

<body>
    @if (session('flash-error'))
        <span class="admin-toastr" onclick="toastr_alert('Error','{{ session()->get('flash-error') }}','error')"></span>
    @endif
    @if (session('flash-success'))
        <span class="admin-toastr"
            onclick="toastr_alert('Success','{{ session()->get('flash-success') }}','success')"></span>
    @endif
    @yield('content')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

    {{-- TOASTR --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

    <script src="{{ asset('assets/js/custom.js') }}"></script>

    @stack('scripts')
</body>

</html>
