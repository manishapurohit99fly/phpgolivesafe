@extends('admin.layout.index')
@section('admin-title', 'My Reports')

@section('content')
<div class="container-fluid admin-list-page">
    <div class="page-content-wrapper">

        {{-- Filter Card --}}
        <div class="card custom-card">
            <div class="card-header section-header">
                <h4 class="mb-0"><i class="fa fa-chart-bar me-2"></i>My Reports</h4>
            </div>

            <div class="card-body section-search">
                <div class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label class="form-label fw-medium">Project <span class="text-danger">*</span></label>
                        <select id="reportProjectSelect"
                                class="form-select"
                                data-no-select2
                                data-search-url="{{ route('user.reports.search') }}"
                                data-assessments-url="{{ route('user.reports.assessment.options') }}"
                                data-preselect="{{ $selectedProjectId ?? '' }}">
                            <option value=""></option>
                            @if(!empty($selectedProject))
                                <option value="{{ $selectedProjectId }}" selected>
                                    {{ $selectedProject->project_name }}{{ $selectedProject->client_name ? ' (' . $selectedProject->client_name . ')' : '' }}
                                </option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-medium">Assessment <span class="text-danger">*</span></label>
                        <select id="reportAssessmentSelect"
                                class="form-select"
                                data-no-select2
                                data-load-url="{{ route('user.reports.assessment.load') }}"
                                data-preselect="{{ $selectedAssessmentId ?? '' }}"
                                disabled>
                            <option value="">— Select a project first —</option>
                            @if(!empty($selectedAssessment))
                                <option value="{{ $selectedAssessmentId }}" selected>{{ $selectedAssessment->name }}</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-4 d-flex gap-2 align-items-end">
                        <button type="button" id="generateReportBtn" class="btn btn-primary flex-grow-1" disabled>
                            <i class="fa fa-magnifying-glass me-1"></i>Generate Report
                        </button>
                        <button type="button" id="resetReportBtn" class="btn btn-outline-secondary px-3" title="Reset filters">
                            <i class="fa fa-rotate-left"></i>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- Report Output — hidden until Generate Report is clicked --}}
        <div id="reportContainer" class="d-none mt-3"></div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script src="{{ asset('assets/js/assessment-report-select.js') }}"></script>
@endpush
