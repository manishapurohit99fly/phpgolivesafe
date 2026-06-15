@extends('admin.auth.index')
@section('content')
@section('admin-title', 'Reset Password')

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
                <div class="text-center mb-5">
                    <img src="{{($siteSetting?->site_logo) ? asset($siteSetting->site_logo) : asset('assets/images/sidebarlogo.svg'); }}">
                </div>
                <form method="post" action="{{ route('admin.resetPassword', $token) }}" id="resetPasswordForm">
                    @csrf
                    <div class="whiteBg">
                        <h4>Reset Password</h4>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" class="form-control form-control-lg no-space" id="new_password"
                                    placeholder="Enter password" name="new_password" value="">
                                <span class="toggle-password">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                            @error('new_password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new-password-confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" class="form-control form-control-lg no-space" 
                                    id="new-password-confirmation" placeholder="Enter confirm password"
                                    name="new_password_confirmation" value="">
                                <span class="toggle-password">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                            @error('new_password_confirmation')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="btn-block">
                            <button type="submit" class="btn btn-lg btn-primary w-100">
                                Continue
                            </button>
                        </div>
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

    // Strong password method
    $.validator.addMethod("strongPassword", function(value, element) {
        return this.optional(element) ||
            /[A-Z]/.test(value) &&    // has uppercase
            /[a-z]/.test(value) &&    // has lowercase
            /[0-9]/.test(value) &&    // has number
            /[!@#$%^&*(),.?":{}|<>]/.test(value) && // has special char
            !/\s/.test(value);        // no spaces
    }, "Password must include uppercase, lowercase, number, special character and no spaces.");

    // Reset Password Form validation
    $("#resetPasswordForm").validate({
        rules: {
            new_password: {
                required: true,
                minlength: 8,
                maxlength: 15,
                strongPassword: true
            },
            new_password_confirmation: {
                required: true,
                equalTo: "#new_password"
            }
        },
        messages: {
            new_password: {
                required: "Please enter your new password",
                minlength: "Password must be at least 8 characters long",
                maxlength: "Password must not exceed 15 characters"
            },
            new_password_confirmation: {
                required: "Please confirm your password",
                equalTo: "Passwords do not match"
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
});
</script>
@endpush
