@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Users')

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row">
            <a href="{{ route('admin.userIndex') }}" class="btn-back" title="Back to Users">
                <i class="fa fa-arrow-left"></i>
                <span class="btn-back-label">Back</span>
            </a>
            <div class="page-title-row-text">
                <h4>Edit User</h4>
                <p class="page-title-row-sub">Update profile details, contact info, and account status.</p>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="whiteBg">
                    <form action="{{ enroute('admin.users.userUpdate', $singleUser->id) }}" method="POST"
                        enctype="multipart/form-data" id="addUserForm"
                        data-check-mail-url="{{ route('admin.checkEmail') }}"
                        data-check-phone-url="{{ route('admin.checkPhone') }}"
                        data-user-id="@eid($singleUser->id)">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label for="first-name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control no-leading-space" name="first_name"
                                    value="{{ $singleUser->first_name }}" placeholder="Enter first name"
                                    id="first-name" maxlength="50" >
                                @error('first_name')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last-name" class="form-label">Last Name</label>
                                <input type="text" class="form-control no-leading-space" name="last_name"
                                    value="{{ $singleUser->last_name }}" placeholder="Enter last name" id="last-name">
                                @error('last_name')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone-number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control no-space" name="phone_no"
                                    value="{{ $singleUser->phone_no }}" placeholder="Enter phone number" id="phone-number" maxlength="10"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                @error('phone_no')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control no-space" name="email"
                                    value="{{ $singleUser->email }}" placeholder="Enter email address" id="email" maxlength="64">
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
                                        data-preview="#edit-user-photo-preview">
                                        <img id="edit-user-photo-preview" src="{{ $singleUser->profile_photo ? asset($singleUser->profile_photo) : '' }}" alt="">
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
                                    <option value="1" {{ $singleUser->status == '1' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="0" {{ $singleUser->status == '0' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                                @error('status')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-12 d-flex justify-content-end gap-2">                            
                                <a href="{{ route('admin.userIndex') }}" class="btn btn-outline-primary">Cancel</a>
                                <button type="submit" id="submitBtn" class="btn btn-primary">Update</button>
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