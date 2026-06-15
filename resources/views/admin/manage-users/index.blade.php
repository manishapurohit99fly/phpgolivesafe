@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Users')
@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="container-fluid admin-list-page">
    {{-- The view-mode radio inputs sit at the top of the page wrapper so the
         CSS sibling/`:has()` selector can flip the listing between Table and
         Card layouts without any JavaScript. --}}
    <input type="radio" name="user-view-mode" id="ulm-view-table" class="user-view-toggle-input" checked>
    <input type="radio" name="user-view-mode" id="ulm-view-card"  class="user-view-toggle-input">

    <div class="page-content-wrapper user-listing-page">

        <div class="card custom-card">

            <div class="card-header section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">Manage Users</h4>
                    <small class="text-muted d-block mt-1">View, search, and manage all user accounts in one place.</small>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2 ms-auto">
                    <div class="view-toggle" role="group" aria-label="Switch list view">
                        <label for="ulm-view-table" class="view-toggle-btn" data-view="table" title="Table view">
                            <i class="fa fa-list"></i>
                            <span class="view-toggle-label">Table</span>
                        </label>
                        <label for="ulm-view-card" class="view-toggle-btn" data-view="card" title="Card view">
                            <i class="fa fa-th-large"></i>
                            <span class="view-toggle-label">Cards</span>
                        </label>
                    </div>
                    <a href="{{ route('admin.users.userAdd') }}" class="btn btn-primary mb-0">
                        <i class="fa fa-plus me-1"></i> Add User
                    </a>
                </div>
            </div>

            <div class="card-body section-search">
                <div class="row g-3">

                    <div class="col-md-2">
                        <label class="form-label">Keyword</label>
                        <input type="text" id="search-keyword" class="form-control no-leading-space"
                            placeholder="Search Name/Email/Phone">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" id="start-date" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" id="end-date" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select id="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button id="search-btn" class="btn btn-primary w-100">Search</button>
                        <button id="reset-btn" class="btn btn-outline-primary w-100">Reset</button>
                        <a href="javascript:void(0)" id="export-btn" class="btn btn-primary w-100">
                            <i class="fa fa-download me-1"></i> Export CSV
                        </a>
                    </div>

                </div>
            </div>

            <div class="card-body section-table">
                <div class="table-responsive user-listing-wrapper">
                    <table class="table theme-table align-middle datatable-ajax user-listing-table"
                        data-user-list
                        data-url="{{ route('admin.users.datatable') }}"
                        data-export-url="{{ route('admin.users.userExport') }}">
                        <thead>
                            <tr>
                                <th class="col-narrow">S. No.</th>
                                <th class="col-image">Image</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone No</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th class="col-actions">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>

{{-- Reset Password Modal --}}
<div class="modal fade" id="resetPasswordModal" tabindex="-1"
     data-bs-backdrop="static" data-bs-keyboard="false"
     aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="resetPasswordForm" method="POST" action="{{ route('admin.users.userResetPassword') }}" autocomplete="off" novalidate>
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="resetPasswordModalLabel">
                        <i class="fa fa-key text-primary"></i> Reset User Password
                    </h5>
                    <button type="button" class="btn-close" id="resetPasswordCloseBtn" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="d-flex align-items-center mb-3 p-3 rounded" style="background:#f8f9fb;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width:42px;height:42px;background:#eef0ff;color:var(--theme-primary,#4F46E5);font-weight:600;">
                            <i class="fa fa-user"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" id="reset-user-name">-</div>
                            <small class="text-muted" id="reset-user-email">-</small>
                        </div>
                    </div>

                    <input type="hidden" name="user_id" id="reset_user_id" value="">

                    <div class="mb-3">                     
                        <label for="reset_password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="position-relative">
                                <input type="password" class="form-control form-control-lg no-space" placeholder="New Password"
                                    name="password" id="reset_password" value="">
                                <span class="toggle-password">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                    </div>

                    <div class="mb-2">
                        <label for="reset_password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <div class="position-relative">
                                <input type="password" class="form-control form-control-lg no-space" placeholder="Confirm Password"
                                    name="password_confirmation" id="reset_password_confirmation" value="">
                                <span class="toggle-password">
                                    <i class="fa fa-eye"></i>
                                </span>                            
                        </div>  
                    </div>

                    <div class="alert alert-info mt-3 mb-0 py-2 px-3 small d-flex align-items-start gap-2">
                        <i class="fa fa-info-circle mt-1"></i>
                        <span>The user will receive a notification email with the new password. The modal will close only after both the password update and the email are delivered.</span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="resetPasswordCancelBtn">Cancel</button>
                    <button type="submit" id="resetPasswordSubmitBtn" class="btn btn-primary">
                        <i class="fa fa-paper-plane me-1"></i> Update &amp; Notify
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/user.js') }}"></script>
@endpush
