@extends('admin.layout.index')
@section('admin-title', 'My Assigned Projects')

@section('content')
<div class="container-fluid admin-list-page">
    <div class="page-content-wrapper">

        <div class="card custom-card">
            <div class="card-header section-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fa fa-diagram-project me-2"></i>My Assigned Projects</h4>
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
                        id="userProjectsTable"
                        data-url="{{ route('user.project.datatable') }}">
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
<script>
$(function () {
    var table = $('#userProjectsTable').DataTable({
        processing: true,
        serverSide: true,
        searching:  false,
        ordering:   false,
        dom: "<'row'<'col-12'tr>><'row datatable-footer'<'col-auto'l><'col-12 col-md' i><'col-12 col-md-auto'p>>",
        ajax: {
            url: $('#userProjectsTable').data('url'),
            data: function (d) {
                d.keyword = $('#search-keyword').val();
                d.status  = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',  orderable: false, searchable: false },
            { data: 'project_name', orderable: false },
            { data: 'client_name',  orderable: false },
            { data: 'status',       orderable: false },
            { data: 'created_at',   orderable: false },
            { data: 'action',       orderable: false }
        ]
    });

    $('#search-btn').on('click', function () { table.ajax.reload(); });
    $('#reset-btn').on('click', function () {
        $('#search-keyword').val('');
        $('#filter-status').val('');
        table.ajax.reload();
    });
    $('#search-keyword').on('keydown', function (e) {
        if (e.key === 'Enter') table.ajax.reload();
    });
});
</script>
@endpush
