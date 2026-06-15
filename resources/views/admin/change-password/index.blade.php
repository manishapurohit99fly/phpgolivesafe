@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Change Password')

<div class="container-fluid pt-3">
    <div class="page-content-wrapper">

        <div class="page-title-row">
            <div class="page-title-row-text">
                <h4>Change Password</h4>
                <p class="page-title-row-sub">Update the password used to sign in to the admin panel.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-6">
                <div class="card custom-card">
                    <div class="section-header-bar">
                        <h5><i class="fa fa-lock me-2 text-muted"></i>Account Security</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.changePassword.update') }}" method="post" id="change-password-form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="current-password" class="form-label">
                                        Current Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control no-space" name="current_password"
                                            placeholder="Enter current password" id="current-password">
                                        <span class="toggle-password">
                                            <i class="fa fa-eye"></i>
                                        </span>
                                    </div>
                                    @error('current_password')
                                        <div class="form-valid-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="new-password" class="form-label">
                                        New Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control no-space" name="new_password"
                                            placeholder="Enter new password" id="new-password">
                                        <span class="toggle-password">
                                            <i class="fa fa-eye"></i>
                                        </span>
                                    </div>
                                    @error('new_password')
                                        <div class="form-valid-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="confirm-password" class="form-label">
                                        Confirm Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control no-space" name="new_password_confirmation"
                                            placeholder="Enter confirm password" id="confirm-password">
                                        <span class="toggle-password">
                                            <i class="fa fa-eye"></i>
                                        </span>
                                    </div>
                                    @error('new_password_confirmation')
                                        <div class="form-valid-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        Update Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        

    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('assets/js/change-password.js') }}"></script>
@endpush

