<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('page-title', 'Project Report')</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    @stack('styles')
    <style>
        body { background: #f3f4f6; color: #1f2937; }
        .public-navbar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 14px 0;
        }
        .public-navbar-brand { font-weight: 700; font-size: 1.1rem; color: #2563eb; text-decoration: none; }
        .public-navbar-badge {
            font-size: 0.72rem;
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            border-radius: 20px;
            padding: 3px 10px;
        }
        .public-footer {
            border-top: 1px solid #e5e7eb;
            padding: 20px 0;
            text-align: center;
            font-size: 0.82rem;
            color: #9ca3af;
            background: #fff;
            margin-top: 40px;
        }
    </style>
</head>
<body>

<nav class="public-navbar">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <a href="#" class="public-navbar-brand">
                <i class="fa fa-diagram-project me-2"></i>Deployment Checklist
            </a>
            <span class="public-navbar-badge"><i class="fa fa-eye me-1"></i>View Only</span>
        </div>
    </div>
</nav>

<main class="container py-4">
    @yield('content')
</main>

<footer class="public-footer">
    <div class="container">
        This report is publicly shared. For any questions contact the project team.
    </div>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@stack('scripts')
</body>
</html>
