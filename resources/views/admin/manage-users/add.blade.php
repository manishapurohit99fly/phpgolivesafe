@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Users')

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row">
            <!-- <a href="{{ route('admin.users.userIndex') }}" class="btn-back" title="Back to Users">
                <i class="fa fa-arrow-left"></i>
                <span class="btn-back-label">Back</span>
            </a> -->
            <div class="page-title-row-text">
                <h4>Add User</h4>
                <p class="page-title-row-sub">Create a new user account and assign access details.</p>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="whiteBg">
                    <form action="{{ route('admin.users.userSave') }}" method="post" enctype="multipart/form-data" id="addUserForm" data-check-mail-url="{{ route('admin.users.checkEmail') }}" data-check-phone-url="{{ route('admin.users.checkPhone') }}"  >
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label for="first-name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control no-leading-space" name="first_name"
                                    value="{{ old('first_name') }}" placeholder="Enter first name" id="first-name" maxlength="50">
                                @error('first_name')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last-name" class="form-label">Last Name</label>
                                <input type="text" class="form-control no-leading-space" name="last_name"
                                    value="{{ old('last_name') }}" placeholder="Enter last name" id="last-name" maxlength="50">
                                @error('last_name')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone-number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control phone" name="phone_no"
                                    value="{{ old('phone_no') }}" placeholder="Enter phone number" id="phone-number" maxlength="15" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                @error('phone_no')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control no-space" name="email" value="{{ old('email') }}"
                                    placeholder="Enter email address" id="email" maxlength="64">
                                @error('email')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            
                            <div class="col-md-6 mb-3">
                                <label for="profile_photo" class="form-label">Profile Photo</label>
                                <div class="img-upload-group">                                    
                                    <input type="file" class="form-control" name="profile_photo" id="profile_photo"
                                        accept=".jpg,.jpeg,.png"
                                        data-crop-ratio="1"
                                        data-crop-width="400"
                                        data-crop-height="400"
                                        data-min-crop-width="180"
                                        data-min-crop-height="180"
                                        data-preview="#add-user-photo-preview">

                                    <img id="add-user-photo-preview" src="" alt="" style="" data-original-src="">
                                </div>
                                <small class="text-muted d-block mt-1">
                                    Recommended: <strong>400×400</strong> (square), max <strong>2 MB</strong>. Formats: <strong>JPG, PNG, WebP</strong>.
                                </small>

                                @error('profile_photo')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select id="status" name="status" class="form-select">
                                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                                @error('status')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>
                          
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    Password <span class="text-danger">*</span>
                                    <small class="text-muted">(6–15 chars)</small>
                                </label>
                                <div class="position-relative">
                                    <input type="password" class="form-control no-space"
                                        name="password" id="password" maxlength="15"
                                        placeholder="Enter password" autocomplete="new-password">
                                    <span class="toggle-password">
                                        <i class="fa fa-eye"></i>
                                    </span>
                                </div>
                                @error('password')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="password" class="form-control no-space"
                                        name="password_confirmation" id="password_confirmation"
                                        maxlength="15" placeholder="Re-enter password"
                                        autocomplete="new-password">
                                    <span class="toggle-password">
                                        <i class="fa fa-eye"></i>
                                    </span>
                                </div>
                                @error('password_confirmation')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="alert alert-info py-2 px-3 small mb-0 d-flex align-items-start gap-2">
                                    <i class="fa fa-info-circle mt-1"></i>
                                    <span>
                                        A welcome email with the user's sign-in details
                                        will be sent automatically once the account is created.
                                    </span>
                                </div>
                            </div>
                        
                        </div>
                        <div class="row">
                            <div class="col-12 d-flex justify-content-end gap-2">                                
                                <a href="{{ route('admin.users.userIndex') }}" class="btn btn-outline-primary">Cancel</a>
                                <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
                                <button type="button" id="resetForm" class="btn btn-outline-primary">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
    <script src="{{ asset('assets/js/user.js') }}"></script>
@endpush
