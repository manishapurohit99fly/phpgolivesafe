@extends('admin.auth.index')
@section('content')
@section('admin-title', 'Login')

<div class="row g-0 login-wrapper">
    <div class="col-lg-6 login-left">
        <div class="text-center loginLeftImg"
             @if(!empty($siteSetting?->auth_side_banner))
                style="background-image: url('{{ asset($siteSetting->auth_side_banner) }}');"
             @endif></div>
    </div>
    <div class="col-lg-6  col-md-12 login-right">
        <div class="loginRightImg">
            <div class="login-container">
                <div class="text-center mb-5"><img src="{{($siteSetting?->site_logo) ? asset($siteSetting->site_logo) : asset('assets/images/sidebarlogo.svg'); }}"></div>
                <form method="post" action="{{ route('admin.loginAuth') }}" id="loginForm">
                    @csrf
                    <div class="whiteBg">
                        <h4>Sign In</h4>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-lg no-space" placeholder="Email Address"
                                    name="email" value="" maxlength="64">
                            </div>
                            @error('email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="passwor-field" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" class="form-control form-control-lg no-space" placeholder="Password"
                                    name="password" id="passwor-field" value="">
                                <span class="toggle-password">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                            @error('password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-space-between">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value=""
                                        id="flexCheckDefault">
                                    <label class="form-check-label" for="flexCheckDefault">
                                        Remember me
                                    </label>
                                </div>

                                <a href="{{ route('admin.forgotPassword') }}" class="forgotPass ms-auto">Forgot Password?</a>

                            </div>
                        </div>
                        <button type="submit" class="btn btn-lg btn-primary w-100">
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection
@push('scripts')
<script>
    $(function() {
        $("#loginForm").validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minlength: 6
                }
            },
            messages: {
                email: {
                    required: "Please enter your email address",
                    email: "Please enter a valid email address"
                },
                password: {
                    required: "Please enter your password",
                    minlength: "Password must be at least 6 characters long"
                }
            },
            errorElement: 'div',
            errorClass: 'text-danger',
            errorPlacement: function (error, element) {
                var $wrap = element.closest('.position-relative');
                if ($wrap.length) {
                    error.insertAfter($wrap);
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function(element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid');
            }
        });

        // Password visibility toggle is bound globally in custom.js

        @if(session('error'))
        toastr.error('{{ session('error') }}');
        @endif
    });
</script>
@endpush
