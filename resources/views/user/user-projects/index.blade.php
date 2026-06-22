@extends('admin.layout.index')
@section('admin-title', 'My Assigned Projects')

@section('content')
<div class="container-fluid admin-list-page">
    <div class="page-content-wrapper">

        <div class="card custom-card">
            <div class="card-header section-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fa fa-diagram-project me-2"></i>My Assigned Projects
                    <span class="badge bg-secondary-subtle text-secondary fw-normal ms-2"
                          id="projectCountBadge" style="display:none;font-size:.8rem!important;vertical-align:middle;"></span>
                </h4>
            </div>

            {{-- Search --}}
            <div class="card-body section-search">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Search</label>
                        <input type="text" id="search-keyword" class="form-control no-leading-space"
                            placeholder="Project name or client name..." maxlength="200">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button id="search-btn" class="btn btn-primary w-100">Search</button>
                        <button id="reset-btn" class="btn btn-outline-primary w-100">Reset</button>
                    </div>
                </div>
            </div>

            {{-- Project Accordion --}}
            <div class="card-body section-table">
                <div class="dataTables_wrapper">

                    <div id="userProjectAccordion">
                        <div class="text-center py-5 text-muted">
                            <div class="spinner-border text-secondary mb-3" role="status" style="width:1.75rem;height:1.75rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mb-0 small">Loading projects...</p>
                        </div>
                    </div>
                    <div id="userProjectEmpty" class="text-center py-5 text-muted d-none">
                        <i class="fa fa-folder-open fa-2x mb-3 d-block"></i>
                        <p class="mb-0">No projects with assigned assessments found.</p>
                    </div>

                    {{-- Pagination — exact DataTable datatable-footer structure --}}
                    <div id="projectPagination" class="datatable-footer d-none">
                        <div class="dataTables_info">
                            Showing <span id="pagFrom"></span> to <span id="pagTo"></span>
                            of <span id="pagTotal"></span> entries
                        </div>
                        <div class="dataTables_paginate paging_simple_numbers">
                            <ul id="projectPaginationList" class="pagination mb-0"></ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
/* ── Project accordion ──────────────────────────────────────────── */
.project-accordion-item {
    background: var(--surface-card, #fff);
    border: 1px solid var(--border-softer, #e9ecef);
    border-radius: var(--radius-md, 8px);
    margin-bottom: 6px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(0,0,0,.03);
    transition: box-shadow .15s ease, border-color .15s ease;
}
.project-accordion-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    border-color: var(--border-soft, #d1d5db);
}
.project-row {
    padding: 10px 14px 10px 16px;
    background: var(--surface-card, #fff);
    position: relative;
}
.project-row::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
}
.project-row.status-active::before   { background: linear-gradient(180deg,#22c55e,#16a34a); }
.project-row.status-inactive::before { background: var(--border-soft, #94a3b8); }
.project-row:hover { background: var(--surface-hover, #f8faff); }
.project-name { font-size: .875rem; font-weight: 600; color: var(--text-base, #1e293b); }
.project-meta { font-size: .775rem; color: var(--text-muted, #64748b); margin-top: 1px; }
.toggle-icon  { font-size: .7rem; color: var(--text-muted, #6c757d); transition: transform .2s ease; }
.toggle-icon.open { transform: rotate(90deg); }

/* ── Assessment nested table ────────────────────────────────────── */
.assessment-panel { padding: 0 0 8px; }
.assessment-panel .inner-table { margin: 0; font-size: .875rem; }
.assessment-panel .inner-table thead th {
    background: var(--surface-muted, #f1f5f9); color: var(--text-soft, #475569);
    font-weight: 600; font-size: .775rem; text-transform: uppercase; letter-spacing: .04em;
    padding: 8px 12px; border-top: none;
}
.assessment-panel .inner-table tbody td { padding: 9px 12px; vertical-align: middle; border-color: var(--border-softer, #f1f3f5); }
.assessment-panel .inner-table tbody tr:last-child td { border-bottom: none; }
.assessment-panel .inner-table tbody tr:hover { background: var(--surface-muted, #fafbfc); }

/* Neutralise Bootstrap .page-link inside the DataTable-styled paginator ── */
#projectPagination .page-link {
    border: none !important;
    border-radius: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    background: transparent !important;
    color: inherit !important;
    box-shadow: none !important;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/user-project.js') }}"></script>
<script>
    window.userProjectListUrl    = "{{ route('user.project.list.ajax') }}";
    window.userForProjectBaseUrl = "{{ url('user/user-assessments/for-project') }}";
</script>
@endpush
