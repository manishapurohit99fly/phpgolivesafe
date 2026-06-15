@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Profile')
   <div class="container-fluid pt-3  children-detail">
        <div class="page-content-wrapper">           
            <div class="container d-flex justify-content-center mt-4">
                <div class="row w-100">                        
                    <div class="col-md-8 col-lg-7 mx-auto">
                        <div class="card shadow-sm" style="border-radius: 10px;">
                            <div
                                class="card-header text-white p-3 text-center"
                                style="border-radius: 10px 10px 0 0"
                            >
                                <h5 class="mb-0">Edit Profile</h5>
                            </div>
                            <div class="card-body">
                                <form id="profileForm" enctype="multipart/form-data" method="POST" action="{{ route('admin.updateProfile') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4 user-im text-center position-relative">
                                            
                                            <div
                                                id="profileImageContainer"
                                              
                                            >
                                                <img
                                                    id="previewImage"
                                                    src="{{ get_avatar($currentUserInfo->profile_photo) }}"
                                                    alt="img"
                                                    style="width: 100%; height: 100%; object-fit: cover;border-radius: 50%;"
                                                />
                                                <input
                                                    type="file"
                                                    name="profile_photo"
                                                    id="profile_photo"
                                                    class="d-none"
                                                    accept=".jpg,.jpeg,.png,.webp"
                                                    data-crop-ratio="1"
                                                    data-crop-width="400"
                                                    data-crop-height="400"
                                                    data-min-crop-width="180"
                                                    data-min-crop-height="180"
                                                    data-preview="#previewImage"
                                                />
                                                <label for="profile_photo" class="edit-icon">
                                                    <i class="fa fa-pencil"></i>
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                Recommended: <strong>400×400</strong> (square), max <strong>2 MB</strong>. Formats: <strong>JPG, PNG, WebP</strong>.
                                            </small>
                                            <div class="text-danger error-profile_photo mt-2"></div>
                                            </div>

                                        <div class="col-md-8 user-detail">
                                            <div class="ms-3">
                                                <div class="mb-3">
                                                    <label
                                                        class="form-label"
                                                        for="email"
                                                        style="font-weight: 600; font-size: 14px"
                                                    >
                                                        Email Address
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input
                                                        type="email"
                                                        class="form-control bg-light"
                                                        value="{{ $currentUserInfo->email ?? '-' }}"
                                                        id="email"
                                                        readonly
                                                    />
                                                </div>


                                                <div class="mb-3">
                                                    <label class="form-label" style="font-weight: 600; font-size: 14px;">Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control no-leading-space" name="name"
                                                        value="{{ $currentUserInfo->first_name }}" placeholder="Enter name"
                                                        id="first-name" maxlength="50">
                                                    <div class="text-danger error-name"></div>
                                                </div>
                                              
                                            </div>

                                            <div class="ms-3 mt-5 d-flex justify-content-end">
                                                <a
                                                    href="{{ route('admin.dashboard') }}"
                                                    class="btn btn-outline-primary me-3"
                                                >                                                    
                                                    Cancel
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    Update Profile
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>                                
                            </div>
                        </div>
                    </div>
            
                </div>                                   
            </div>              
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/profile.js') }}"></script>
@endpush
