
$(function() {
    // Form validation

    $.validator.addMethod("strongPassword", function(value, element) {
            return this.optional(element) ||
                /[A-Z]/.test(value) // has uppercase
                &&
                /[a-z]/.test(value) // has lowercase
                &&
                /[0-9]/.test(value) // has number
                &&
                /[^A-Za-z0-9]/.test(value); // has special char
        },
        "Password must include at least one uppercase letter, one lowercase letter, one number, and one special character."
    );


    $("#change-password-form").validate({
        rules: {
            current_password: {
                required: true,
            },
            new_password: {
                required: true,
                minlength: 8,
                strongPassword: true
            },
            new_password_confirmation: {
                required: true,
                equalTo: "#new-password"
            }
        },
        messages: {
            current_password: {
                required: "Please enter your current password"
            },
            new_password: {
                required: "Please enter your new password",
                minlength: "Password must be at least 8 characters long"
            },
            new_password_confirmation: {
                required: "Please confirm your new password",
                equalTo: "Confirm Password does not match"
            }
        },
        errorElement: 'div',
        errorClass: 'text-danger',
        errorPlacement: function(error, element) {
            if (element.parent().hasClass('position-relative')) {
                error.insertAfter(element.parent());
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
