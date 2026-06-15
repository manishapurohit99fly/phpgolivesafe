@extends('admin.layout.index')
@section('admin-title', 'Projects')

@section('content')
<div class="container-fluid admin-list-page">
    <div class="page-content-wrapper">

        <div class="card custom-card">
            <div class="card-header section-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Projects</h4>
                <a href="{{ route('admin.project.create') }}" class="btn btn-primary mb-2">
                    <i class="fa fa-plus me-1"></i> Add Project
                </a>
            </div>

            <div class="card-body section-search">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" id="search-keyword" class="form-control no-leading-space"
                            placeholder="Project name or client name..." maxlength="200">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select id="filter-status" class="form-control">
                            <option value="">All</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button id="search-btn" class="btn btn-primary w-100">Search</button>
                        <button id="reset-btn" class="btn btn-outline-primary w-100">Reset</button>
                    </div>
                </div>
            </div>

            <div class="card-body section-table">
                <div class="table-responsive">
                    <table class="table theme-table align-middle datatable-ajax"
                        data-project-list
                        data-url="{{ route('admin.project.datatable') }}">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Project Name</th>
                                <th>Client Name</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Action</th>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/project.js') }}"></script>
@endpush
