@extends('admin.layout.index')
@section('admin-title', 'Assessment Report')

@section('content')
<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">

        <div class="page-title-row d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h4 class="mb-0 ms-3"><i class="fa fa-chart-bar me-2"></i>Assessment Report</h4>
            <a href="{{ $backUrl ?? route('user.reports.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                <i class="fa fa-arrow-left me-1"></i>Back to Reports
            </a>
        </div>

        @include('partials.assessment-report', ['reportData' => $reportData])

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
@endpush
