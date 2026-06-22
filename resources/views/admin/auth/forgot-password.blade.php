@extends('admin.auth.index')
@section('content')
@section('admin-title', 'Forgot Password')

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
                    <img src="{{($siteSetting?->site_logo) ? asset($siteSetting->site_logo) : asset('assets/images/sidebarlogo.svg'); }}"></div>
                <form method="post" action="{{ route('admin.sendResetToken') }}" id="forgotPasswordForm">
                    @csrf
                    <div class="whiteBg">
                        <h4>Forgot Password</h4>
                        <p>Please enter your email address, you will receive a link to create a new password via email
                        </p>
                        <div class="mb-3">
                            <label for="exampleInputEmail1" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-lg no-space" placeholder="Email address"
                                    name="email" value="" maxlength="64">
                            </div>
                            @error('email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="btn-block">
                            <button type="submit" class="btn btn-lg btn-primary w-100">
                                Send
                            </button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.login') }}" class="w-100 mt-2"> Back to Login </a>
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
        $("#forgotPasswordForm").validate({
            rules: {
                email: {
                    required: true,
                    email: true
                }
            },
            messages: {
                email: {
                    required: "Please enter your email address",
                    email: "Please enter a valid email address"
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
    });
</script>
@endpush
