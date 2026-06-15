const TemplateForm = (function () {

    return {

        init: function () {
            this.initValidation();
        },

        initValidation: function () {

            $("#addTemplateForm").validate({

                rules: {
                    name: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
                    },
                    subject: {
                        required: true,
                        minlength: 3,
                        maxlength: 255
                    },
                    body: {
                        required: true
                    },
                    status: {
                        required: true
                    }
                },

                messages: {
                    name: {
                        required: "Enter template identifier",
                        minlength: "Minimum 3 characters"
                    },
                    subject: {
                        required: "Enter subject"
                    },
                    body: {
                        required: "Enter email body"
                    },
                    status: {
                        required: "Select status"
                    }
                },

                errorElement: "div",
                errorClass: "text-danger",

                highlight: function (el) {
                    $(el).addClass("is-invalid");
                },

                unhighlight: function (el) {
                    $(el).removeClass("is-invalid");
                },

                submitHandler: function (form) {
                    TemplateForm.submitAjax(form);
                    return false;
                }
            });
        },

        // Shared save flow — kept in lockstep with UserForm.submitAjax in
        // user.js. Both modules MUST behave identically:
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
            if (typeof window.ckEditorInstance !== "undefined" && window.ckEditorInstance) {
                const $body = $("#body");
                if ($body.length) $body.val(window.ckEditorInstance.getData());
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
        }

    };

})();


/*
 * TemplateList module
 *
 * Drives the Email Templates index page:
 *   - server-side DataTable
 *   - search / reset filters
 *   - delete via SweetAlert (called from row HTML built in the controller)
 *
 * URLs come from data-* attributes on the table so this file stays static.
 */
const TemplateList = (function () {

    var table;

    return {

        init: function () {
            var $table = $('table[data-template-list]');
            if (!$table.length) return;

            this.initTable($table);
            this.bindFilters();
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
                        d.keyword = $('#search-keyword').val();
                        d.status  = $('#status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name' },
                    { data: 'subject' },
                    { data: 'status', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });
        },

        bindFilters: function () {

            $('#search-btn').on('click', function (e) {
                e.preventDefault();
                if (table) table.draw();
            });

            $('#reset-btn').on('click', function (e) {
                e.preventDefault();
                $('#search-keyword').val('');
                $('#status').val('');
                if (table) table.draw();
            });
        },

        // Called from the inline onclick rendered by AdminEmailTemplateController::datatable()
        deleteTemplate: function (id, url) {

            if (typeof Swal === 'undefined') return;

            Swal.fire({
                title: 'Delete Template?',
                text:  'This action cannot be undone.',
                icon:  'warning',
                showCancelButton:   true,
                confirmButtonColor: '#d33',
                cancelButtonColor:  '#6c757d',
                confirmButtonText:  'Yes, delete it!'
            }).then(function (result) {

                if (!result.isConfirmed) return;

                $.ajax({
                    url:  url,
                    type: 'POST',
                    data: {
                        id: id,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            toastr_alert('Success', res.message, 'success');
                            if (table) table.draw();
                        } else {
                            toastr_alert('Error', res.message, 'error');
                        }
                    },
                    error: function () {
                        toastr_alert('Error', 'Something went wrong.', 'error');
                    }
                });
            });
        }
    };

})();


// Globals — the controller-built row HTML calls this directly via inline onclick
window.deleteEmailTemplate = function (id, url) {
    TemplateList.deleteTemplate(id, url);
};


/*
 * TemplateEditor module
 *
 * Initialises CKEditor 5 on the #body textarea and the slug live-preview
 * under the Template Identifier input. Used by both add and edit pages so
 * the logic lives here once instead of being duplicated in each blade.
 *
 * The CKEditor CDN script must be loaded before email-template.js.
 * The editor instance is exposed as window.ckEditorInstance so that
 * TemplateForm.submitAjax (above) can copy the HTML into #body before submit.
 */
const TemplateEditor = (function () {

    return {

        init: function () {
            this.initCKEditor();
            this.initSlugPreview();
        },

        initCKEditor: function () {
            var bodyEl = document.querySelector('#body');
            if (!bodyEl || typeof ClassicEditor === 'undefined') return;

            ClassicEditor
                .create(bodyEl, {
                    toolbar: {
                        items: [
                            'heading', '|',
                            'bold', 'italic', 'underline', 'strikethrough', '|',
                            'fontColor', 'fontBackgroundColor', 'fontSize', '|',
                            'link', '|',
                            'bulletedList', 'numberedList', '|',
                            'outdent', 'indent', '|',
                            'blockQuote', 'insertTable', 'horizontalLine', '|',
                            'alignment', '|',
                            'sourceEditing', '|',
                            'undo', 'redo'
                        ]
                    },
                    language: 'en',
                })
                .then(function (editor) {
                    window.ckEditorInstance = editor;
                })
                .catch(function (error) { console.error(error); });
        },

        initSlugPreview: function () {
            var sourceField = document.getElementById('name');
            var previewEl   = document.getElementById('name-slug-preview');
            if (!sourceField || !previewEl) return;

            var fallback = previewEl.textContent.trim() || 'welcome_email';

            function slugify(value) {
                var s = String(value || '').toLowerCase();
                if (typeof s.normalize === 'function') {
                    s = s.normalize('NFKD').replace(/[\u0300-\u036f]/g, '');
                }
                return s
                    .replace(/[^a-z0-9]+/g, '_')
                    .replace(/^_+|_+$/g, '');
            }

            function refreshSlugPreview() {
                var slug = slugify(sourceField.value);
                previewEl.textContent = slug !== '' ? slug : fallback;
                previewEl.classList.toggle('text-muted', slug === '');
            }

            sourceField.addEventListener('input', refreshSlugPreview);
            sourceField.addEventListener('change', refreshSlugPreview);
            refreshSlugPreview();
        }

    };

})();


$(function () {
    TemplateForm.init();
    TemplateList.init();
    TemplateEditor.init();
});