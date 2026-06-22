@extends('admin.layout.index')
@section('admin-title', 'Assessment List Report')

@section('content')
<div class="container-fluid admin-list-page">
    <div class="page-content-wrapper">

        <div class="card custom-card">
            <div class="card-header section-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fa fa-clipboard-list me-2"></i>Assessment List Report</h4>
            </div>

            <div class="card-body section-search">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Keyword</label>
                        <input type="text" id="search-keyword" class="form-control no-leading-space"
                            placeholder="Project or assessment name...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select id="filter-status" class="form-select" data-no-select2>
                            <option value="">All</option>
                            <option value="pending" {{ ($preStatus ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ ($preStatus ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" id="start-date" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" id="end-date" class="form-control">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button id="search-btn" class="btn btn-primary w-100">Search</button>
                        <button id="reset-btn" class="btn btn-outline-primary w-100">Reset</button>
                    </div>
                </div>
            </div>

            <div class="card-body section-table">
                <div class="table-responsive">
                    <table class="table border-bottom-0 theme-table align-middle assessment-list-table"
                        data-ajax-url="{{ route('admin.reports.assessment-list.ajax') }}">
                        <thead>
                            <tr>
                                <th class="col-narrow">S. No.</th>
                                <th>Project Name</th>
                                <th>Assessment Name</th>
                                <th>Created Date</th>
                                <th>Submitted Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/assessment-list-report.js') }}"></script>
@endpush
