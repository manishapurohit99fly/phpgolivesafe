const UserForm = (function () {

    return {
        init: function () {
            UserForm.initValidation();            
            UserForm.bindEvents();
        },

        // =========================
        // VALIDATION RULES
        // =========================
        initValidation: function () {

         
            $.validator.addMethod("validEmail", function (value, element) {
                return this.optional(element) || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            }, "Please enter a valid email address.");

          
            $.validator.addMethod("strongPassword", function (value) {
                return (
                    /[A-Z]/.test(value) &&
                    /[a-z]/.test(value) &&
                    /[0-9]/.test(value) &&
                    /[!@#$%^&*(),.?":{}|<>]/.test(value) &&
                    !/\s/.test(value)
                );
            }, "Password must contain at least one uppercase letter, one lowercase letter, one number, one special character, and no spaces.");

            // =========================
            // FORM VALIDATION
            // =========================
            $("#addUserForm").validate({
                rules: {
                    first_name: { required: true, minlength: 2, maxlength: 50 },
                    last_name: {  minlength: 2, maxlength: 50 },

                    email: {
                        required: true,
                        validEmail: true,
                        remote: {
                            url: $("#addUserForm").data("check-mail-url"),
                            type: "post",
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                // `user_id` is the current row on edit pages (read from
                                // the form's data attribute) and undefined on the add
                                // page. Sending it lets `checkEmail` exclude the row
                                // we're editing from the duplicate check, so saving an
                                // edit without changing the email isn't blocked.
                                user_id: function () {
                                    return $("#addUserForm").data("user-id") || '';
                                },
                                email: function () {
                                    return $("#email").val();
                                }
                            }
                        }
                    },

                    phone_no: {
                        required: true,
                        remote: {
                            url: $("#addUserForm").data("check-phone-url"),
                            type: "post",
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                user_id: function () {
                                    return $("#addUserForm").data("user-id") || '';
                                },
                                phone_no: function () {
                                    return $("#phone-number").val();
                                }
                            }
                        }
                    },

                    password: {
                        required:  true,
                        minlength: 8,
                        maxlength: 15
                    },
                    password_confirmation: {
                        required: true,
                        equalTo:  "#password"
                    },

                    status: { required: true }
                },

                messages: {
                    first_name: { required: "Enter first name" },
                    last_name: { required: "Enter last name" },
                    email: { required: "Enter email address", remote: "Email already exists" },
                    password: { required: "Enter password" },
                    phone_no: { required: "Enter phone number", remote: "Phone number already exists" },
                    status: { required: "Select status" },
                    password: {
                        required:  "Enter password",
                        minlength: "Password must be at least 8 characters long.",
                        maxlength: "Password may not exceed 15 characters."
                    },
                    password_confirmation: {
                        required: "Enter confirm password",
                        equalTo:  "Password and confirm password do not match."
                    },
                },

                errorElement: "div",
                errorClass: "text-danger",
                errorPlacement: function (error, element) {
                    var $wrap = element.closest('.position-relative');
                    if ($wrap.length) {
                        error.insertAfter($wrap);
                    } else {
                        error.insertAfter(element);
                    }
                },
                highlight: function (el) {
                    $(el).addClass("is-invalid");
                },

                unhighlight: function (el) {
                    $(el).removeClass("is-invalid");
                },

                submitHandler: function (form) {
                    UserForm.submitAjax(form);                    
                    return false;
                }
            });
        },

        // Shared save flow — kept in lockstep with TemplateForm.submitAjax in
        // email-template.js. Both modules MUST behave identically:
        //   – capture the existing button label from the markup so each blade
        //     keeps its own visible label (Submit / Save / Update) untouched,
        //   – swap to "Saving..." while the request is in flight,
        //   – on success → toast + redirect to res.redirect_url,
        //   – on 422 → render server-side field errors inline,
        //   – on any other failure → a generic toast.
        // The CKEditor sync is defensive — only fires on pages that initialise
        // an editor, so this same body works for forms that don't use one.
        submitAjax: function (form) {

            const $form         = $(form);
            const $btn          = $("#submitBtn");
            const originalLabel = $btn.data("original-label") || $btn.text() || "Save";
            $btn.data("original-label", originalLabel);

            // CKEditor sync — only runs on forms that initialise an editor.
            if (typeof ckEditorInstance !== "undefined" && ckEditorInstance) {
                const $body = $("#body");
                if ($body.length) $body.val(ckEditorInstance.getData());
            }

            const formData = new FormData(form);

            $.ajax({
                url: $form.attr("action"),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,

                beforeSend: function () {
                    $btn.prop("disabled", true).text("Saving...");
                },

                success: function (res) {
                    if (res && res.success) {
                        if (res.message) toastr.success(res.message);
                        if (res.redirect_url) {
                            window.location.href = res.redirect_url;
                        }
                    } else {
                        toastr.error((res && res.message) ? res.message : "Something went wrong");
                    }
                },

                error: function (xhr) {

                    // CLEAR OLD ERRORS FIRST (IMPORTANT)
                    $(".invalid-feedback").remove();
                    $(".is-invalid").removeClass("is-invalid");

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {

                        const errors = xhr.responseJSON.errors;

                        $.each(errors, function (key, value) {
                            const input = $(`[name="${key}"]`);
                            input.addClass("is-invalid");
                            input.after(
                                `<div class="invalid-feedback server-error">${value[0]}</div>`
                            );
                        });

                    } else {
                        toastr.error("Something went wrong");
                    }
                },

                complete: function () {
                    $btn.prop("disabled", false).text(originalLabel);
                }
            });

            return false;
        },

       
        // =========================
        // EVENTS
        // =========================
        bindEvents: function () {

            $(document).on("click", ".toggle-password", function () {
                let input = $(this).siblings("input");
                let icon = $(this).find("i");

                if (input.attr("type") === "password") {
                    input.attr("type", "text");
                    icon.removeClass("fa-eye").addClass("fa-eye-slash");
                } else {
                    input.attr("type", "password");
                    icon.addClass("fa-eye").removeClass("fa-eye-slash");
                }
            });

            $(document).on("click", "#resetForm", function () {
                $("#addUserForm")[0].reset();
                $("#addUserForm").validate().resetForm();
                $(".is-invalid").removeClass("is-invalid");
            });
        }
    };

})();


/*
 * UserList module
 *
 * Drives the Manage Users index page:
 *   - server-side DataTable
 *   - search / reset / export with date-range validation
 *   - reset-password modal (opened from row action)
 *
 * URLs come from data-* attributes on the table and modal so this file
 * stays static.
 */
const UserList = (function () {

    var table;
    var resetModalInstance;
    var $resetForm;
    var $resetSubmitBtn;
    var resetSubmitOriginal;

    return {

        init: function () {
            var $table = $('table[data-user-list]');
            if (!$table.length) return;

            this.initTable($table);
            this.bindFilters();
            this.bindExport($table);
            this.bindDateGuards();
            this.initResetPasswordModal();
        },

        initTable: function ($table) {

            table = $table.DataTable({
                processing: true,
                serverSide: true,
                searching:  false,
                ordering:   false,
                dom: "<'row'<'col-12'tr>><'row datatable-footer'<'col-auto'l><'col-12 col-md' i><'col-12 col-md-auto'p>>",
                ajax: {
                    url: $table.data('url'),
                    data: function (d) {
                        d.keyword    = $('#search-keyword').val();
                        d.start_date = $('#start-date').val();
                        d.end_date   = $('#end-date').val();
                        d.status     = $('#status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'image',       orderable: false, searchable: false },
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'phone_no' },
                    { data: 'status',      orderable: false, searchable: false },
                    { data: 'created_at' },
                    { data: 'action',      orderable: false, searchable: false }
                ]
            });
        },

        bindFilters: function () {

            $('#search-btn').on('click', function (e) {
                e.preventDefault();

                var startDate = $('#start-date').val();
                var endDate   = $('#end-date').val();

                if (startDate && !endDate) {
                    toastr_alert('error', 'Please fill End Date!', 'error');
                    return;
                }

                if (!startDate && endDate) {
                    toastr_alert('error', 'Please fill Start Date!', 'error');
                    return;
                }

                if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                    toastr_alert('error', 'Start date cannot be greater than end date!', 'error');
                    return;
                }

                if (table) table.draw();
            });

            $('#reset-btn').on('click', function (e) {
                e.preventDefault();
                $('#search-keyword').val('');
                $('#start-date').val('');
                $('#end-date').val('');
                $('#status').val('');
                if (table) table.draw();
            });
        },

        bindExport: function ($table) {

            var exportUrl = $table.data('export-url');
            if (!exportUrl) return;

            // Keep the export button state in sync with the current result set.
            if (table) {
                table.on('draw', function () {
                    try {
                        var info = table.page.info();
                        var hasRows = info && info.recordsDisplay > 0;
                        $('#export-btn').toggleClass('disabled', !hasRows).prop('disabled', !hasRows);
                    } catch (e) {
                        // If DataTables info is unavailable, leave button enabled.
                    }
                });
            }

            $('#export-btn').on('click', function (e) {
                e.preventDefault();

                // If no rows match current filters, do not call the export endpoint.
                if (table) {
                    try {
                        var info = table.page.info();
                        if (!info || info.recordsDisplay === 0) {
                            if (typeof toastr !== 'undefined') {
                                toastr.error('No records found to export.');
                            } else if (typeof toastr_alert === 'function') {
                                toastr_alert('Error', 'No records found to export.', 'error');
                            } else {
                                alert('No records found to export.');
                            }
                            return;
                        }
                    } catch (err) {
                        // If we can't read info, proceed with export.
                    }
                }

                var params = new URLSearchParams({
                    keyword:    $('#search-keyword').val(),
                    start_date: $('#start-date').val(),
                    end_date:   $('#end-date').val(),
                    status:     $('#status').val()
                });
                window.location.href = exportUrl + '?' + params.toString();
            });
        },

        bindDateGuards: function () {
            // Block keyboard input on date pickers — date must be picked.
            $('#start-date, #end-date').on('keydown', function (e) {
                e.preventDefault();
            });
        },

        initResetPasswordModal: function () {

            var modalEl = document.getElementById('resetPasswordModal');
            if (!modalEl || typeof bootstrap === 'undefined') return;

            $resetForm           = $('#resetPasswordForm');
            $resetSubmitBtn      = $('#resetPasswordSubmitBtn');
            resetSubmitOriginal  = $resetSubmitBtn.html();

            // Static backdrop + no keyboard close: declared on markup, re-asserted here.
            resetModalInstance = new bootstrap.Modal(modalEl, {
                backdrop: 'static',
                keyboard: false
            });

            this.bindResetPasswordEvents();
            this.initResetPasswordValidation();
        },

        bindResetPasswordEvents: function () {

            // Open modal when reset-password icon is clicked (delegated for DataTable rows)
            $(document).on('click', '.reset-password-btn', function () {

                var id    = $(this).data('id');
                var name  = $(this).data('name')  || '';
                var email = $(this).data('email') || '';

                // Reset form state every time the modal opens
                if ($resetForm.data('validator')) {
                    $resetForm.validate().resetForm();
                }
                $resetForm[0].reset();
                $resetForm.find('.is-invalid').removeClass('is-invalid');
                $resetForm.find('.invalid-feedback, .server-error').remove();
                $resetForm.find('input[type="text"][name="password"], input[type="text"][name="password_confirmation"]')
                    .attr('type', 'password');
                $resetForm.find('.toggle-password i').removeClass('fa-eye-slash').addClass('fa-eye');

                $('#reset_user_id').val(id);
                $('#reset-user-name').text(name);
                $('#reset-user-email').text(email);

                resetModalInstance.show();
            });

            // Cancel / close — only allow when not submitting
            $('#resetPasswordCancelBtn, #resetPasswordCloseBtn').on('click', function () {
                if ($resetSubmitBtn.prop('disabled')) {
                    return; // request in flight
                }
                resetModalInstance.hide();
            });
        },

        initResetPasswordValidation: function () {

            $.validator.addMethod('strongPassword', function (value, element) {
                return this.optional(element) || (
                    /[A-Z]/.test(value) &&
                    /[a-z]/.test(value) &&
                    /[0-9]/.test(value) &&
                    /[!@#$%^&*(),.?":{}|<>]/.test(value) &&
                    !/\s/.test(value)
                );
            }, 'Password must include uppercase, lowercase, number, special character and no spaces.');

            $resetForm.validate({
                rules: {
                    password: {
                        required: true,
                        minlength: 8,
                        maxlength: 15,
                        strongPassword: true
                    },
                    password_confirmation: {
                        required: true,
                        equalTo: '#reset_password'
                    }
                },
                messages: {
                    password: {
                        required:  'Enter new password',
                        minlength: 'Password must be at least 8 characters long.',
                        maxlength: 'Password may not exceed 15 characters'
                    },
                    password_confirmation: {
                        required: 'Please confirm the password',
                        equalTo:  'Passwords do not match'
                    }
                },
                errorElement: 'div',
                errorClass:   'invalid-feedback',
                highlight:    function (el) { $(el).addClass('is-invalid'); },
                unhighlight:  function (el) { $(el).removeClass('is-invalid'); },
                errorPlacement: function (error, element) {
                    // Place errors after the input-group when inside one.
                    var $wrap = element.closest('.position-relative');
                    if ($wrap.length) {
                        error.insertAfter($wrap);
                    } else {
                        error.insertAfter(element);
                    }
                },
                submitHandler: function (form) {
                    UserList.submitResetPassword(form);
                    return false;
                }
            });
        },

        submitResetPassword: function (form) {

            var $form = $(form);

            $.ajax({
                url:  $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),

                beforeSend: function () {
                    $resetSubmitBtn.prop('disabled', true)
                        .html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Updating...');
                    $form.find('.server-error').remove();
                    $form.find('.is-invalid').removeClass('is-invalid');
                },

                success: function (res) {
                    if (res && res.status === 'success') {
                        resetModalInstance.hide();
                        if (typeof toastr !== 'undefined') {
                            toastr.success(res.message || 'Password updated successfully.');
                        }
                        if (table) table.draw(false);
                    } else {
                        // Defensive: if server returns 200 but status != success, keep modal open.
                        if (typeof toastr !== 'undefined') {
                            toastr.error((res && res.message) ? res.message : 'Could not reset password.');
                        }
                    }
                },

                error: function (xhr) {
                    // Modal stays open by design — we only close on full success.
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {

                        $.each(xhr.responseJSON.errors, function (field, messages) {
                            var $input  = $form.find('[name="' + field + '"]');
                            $input.addClass('is-invalid');
                            var $group  = $input.closest('.input-group');
                            var $target = $group.length ? $group : $input;
                            $target.after('<div class="invalid-feedback server-error d-block">' + messages[0] + '</div>');
                        });

                    } else {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'Something went wrong. Please try again.';
                        if (typeof toastr !== 'undefined') {
                            toastr.error(msg);
                        }
                    }
                },

                complete: function () {
                    $resetSubmitBtn.prop('disabled', false).html(resetSubmitOriginal);
                }
            });
        }
    };

})();

$(function () {
    UserForm.init();
    UserList.init();
});