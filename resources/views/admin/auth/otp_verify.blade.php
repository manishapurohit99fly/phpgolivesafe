@extends('admin.auth.index')
@section('content')
@section('admin-title', 'Verify OTP')

@push('styles')
    <style>
        /* Additional CSS to ensure disabled state */
        #resendOtpLink.disabled {
            pointer-events: none !important;
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            text-decoration: none !important;
        }

        #resendOtpLink[disabled] {
            pointer-events: none !important;
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            text-decoration: none !important;
        }
    </style>
@endpush

<div class="row g-0 login-wrapper">
    <div class="col-lg-6 login-left">
        <div class="text-center loginLeftImg"
             @if(!empty($siteSetting?->auth_side_banner))
                style="background-image: url('{{ asset($siteSetting->auth_side_banner) }}');"
             @endif></div>
    </div>
    <div class="col-lg-6 col-md-12 login-right">
        <div class="loginRightImg">
            <div class="login-container">
                <div class="text-center mb-5">
                    <img src="{{ ($siteSetting?->site_logo) ? asset($siteSetting->site_logo) : asset('assets/images/sidebarlogo.svg') }}">
                </div>
                <form method="post" action="{{ route('admin.verifyOtp') }}" id="otpForm">
                    @csrf
                    <div class="whiteBg">
                        <div class="text-left mb-3">
                        <h2>Enter OTP</h2>
                        <p class="text-muted">
                            We've sent a 6-digit verification code to your email.
                        </p>
    
                        <div class="mb-3 d-flex justify-content-between">
                            @for ($i = 1; $i <= 6; $i++)
                                <input type="text" maxlength="1"
                                    class="form-control form-control-lg text-center mx-1 otp-input"
                                    style="width: 50px; font-size: 24px;" inputmode="numeric" pattern="[0-9]*"
                                    id="otp{{ $i }}">
                            @endfor
                            {{-- hidden field to store final OTP --}}
                            <input type="hidden" name="otp" id="otpHidden">
                        </div>

                        {{-- error placeholder for JS --}}
                        <div id="otpError" class="text-danger mb-2 d-none"></div>

                        <div class="col-md-12">
                            <div class="mb-1">
                                <p>Didn't receive a code?
                                    <a href="javacript:void(0)" id="resendOtpLink" class="greenALink">Resend OTP</a>
                                    <span id="resendTimer" class="ms-2 d-none"></span>
                                </p>
                                <div id="resendOtpMessage" class="success-message d-none text-success">
                                    OTP has been sent!
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-lg btn-primary w-100 mt-3">
                            Verify & Sign In
                        </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            // Global state management
            let isResending = false;
            let countdownInterval = null;
            const cooldownTime = 30; // seconds

            // ===== OTP Auto-move Logic =====
            $(".otp-input").on("input", function() {
                let $this = $(this);
                $this.val($this.val().replace(/[^0-9]/g, '')); // only digits
                if ($this.val().length === 1) {
                    $this.next(".otp-input").focus();
                }
                updateHiddenOtp();
            });

            $(".otp-input").on("keydown", function(e) {
                let $this = $(this);
                if (e.key === "Backspace" && $this.val() === "") {
                    $this.prev(".otp-input").focus();
                }
            });

            $('.otp-input').on('paste', function (e) {
                e.preventDefault();
                let pasteData = (e.originalEvent || e).clipboardData.getData('text');
                pasteData = pasteData.replace(/[^0-9]/g, '').slice(0, 6);

                let inputs = $('.otp-input');
                for (let i = 0; i < pasteData.length; i++) {
                    inputs.eq(i).val(pasteData[i]);
                }

                updateHiddenOtp();
                inputs.eq(pasteData.length - 1).focus();
            });
            
            function updateHiddenOtp() {
                let otp = "";
                $(".otp-input").each(function() {
                    otp += $(this).val();
                });
                $("#otpHidden").val(otp);
            }

            function clearOtpInputs() {
                $(".otp-input").val("");
                $("#otpHidden").val("");
                $(".otp-input").first().focus();
            }

            // ===== Handle Form Submit via AJAX =====
            $("#otpForm").on("submit", function(e) {
                e.preventDefault();
                updateHiddenOtp();

                if ($("#otpHidden").val().length < 6) {
                    toastr.error("Please provide a valid 6 digit OTP.");
                    clearOtpInputs();
                    return;
                }

                $.ajax({
                    url: $(this).attr("action"),
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            // Redirect directly on success
                            window.location.href = response.redirect_url;
                        }
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || "Something went wrong!";
                        toastr.error(msg);
                        clearOtpInputs();
                    }
                });
            });

            // ===== Resend OTP Functions =====
            function disableResendLink() {
                isResending = true;
                const $link = $("#resendOtpLink");

                // Multiple ways to disable the link
                $link.addClass("disabled")
                    .css({
                        "pointer-events": "none",
                        "opacity": "0.5",
                        "cursor": "not-allowed"
                    })
                    .attr("disabled", true)
                    .prop("disabled", true);
            }

            function enableResendLink() {
                isResending = false;
                const $link = $("#resendOtpLink");

                $link.removeClass("disabled")
                    .css({
                        "pointer-events": "auto",
                        "opacity": "1",
                        "cursor": "pointer"
                    })
                    .removeAttr("disabled")
                    .prop("disabled", false)
                    .text("Resend OTP");
            }

            function startResendCooldown() {
                let timer = cooldownTime;

                // Ensure link stays disabled and shows timer
                disableResendLink();
                $("#resendOtpLink").text("Resend OTP");
                $("#resendTimer").removeClass("d-none").text(`(${timer}s)`);

                // Clear any existing interval
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                }

                countdownInterval = setInterval(function() {
                    timer--;
                    $("#resendTimer").text(`(${timer}s)`);
                    if (timer <= 0) {
                        clearInterval(countdownInterval);
                        countdownInterval = null;
                        enableResendLink();
                        $("#resendTimer").addClass("d-none").text("");
                        isResending = false; // Reset flag when cooldown ends
                    }
                }, 1000);
            }

            // ===== Resend OTP Click Handler =====
            $('#resendOtpLink').on('click', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                // Multiple checks to prevent execution
                if (isResending) {
                    console.log("Already resending OTP, ignoring click");
                    return false;
                }

                if ($(this).hasClass("disabled")) {
                    console.log("Link is disabled, ignoring click");
                    return false;
                }

                if ($(this).prop("disabled")) {
                    console.log("Link is disabled via prop, ignoring click");
                    return false;
                }

                // Set flag immediately to prevent double clicks
                isResending = true;

                const $link = $(this);

                // Disable immediately before AJAX call
                disableResendLink();
                $link.text('Sending...');

                $.ajax({
                    url: "{{ route('admin.resendOtp') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    timeout: 10000, // 10 second timeout
                    success: function(response) {
                        if (response.success) {
                            toastr.success("New OTP has been sent!");
                            clearOtpInputs();
                            startResendCooldown();
                        } else {
                            // Handle server-side errors
                            toastr.error(response.message || "Failed to send OTP");
                            enableResendLink();
                            isResending = false;
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = "Error resending OTP. Please try again.";

                        if (status === 'timeout') {
                            errorMessage = "Request timed out. Please try again.";
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        toastr.error(errorMessage);
                        enableResendLink();
                        isResending = false;
                    }
                });

                return false; // Prevent any default behavior
            });

            // Prevent any other event handlers on the resend link
            $('#resendOtpLink').off('click.duplicate').on('click.duplicate', function(e) {
                if (isResending) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
            });

            // Start cooldown immediately on page load (optional)
            startResendCooldown();

            // Cleanup on page unload
            $(window).on('beforeunload', function() {
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                }
            });
        });
    </script>
@endpush
@endsection
