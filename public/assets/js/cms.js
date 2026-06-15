/*
 * CMS module
 *
 * Drives the CMS Pages admin screens:
 *   - List page  (table[data-cms-list])     -> CmsList
 *   - Add/Edit   (form#cmsForm)             -> CmsForm
 *
 * URLs and labels come from data-* attributes on the host elements,
 * so this file is static and contains no Blade output.
 */

const CmsList = (function () {

    var table;

    return {

        init: function () {
            var $table = $('table[data-cms-list]');
            if (!$table.length) return;
            this.initTable($table);
            this.bindEvents();
        },

        initTable: function ($table) {
            table = $table.DataTable({
                processing: true,
                serverSide: true,
                searching:  false,
                ordering:   false,
                ajax: {
                    url: $table.data('url'),
                    data: function (d) {
                        d.keyword = $('#search-keyword').val();
                        d.status  = $('#status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'title' },
                    { data: 'status', orderable: false },
                    { data: 'action', orderable: false }
                ]
            });
        },

        bindEvents: function () {

            $('#search-btn').on('click', function () {
                if (table) table.ajax.reload();
            });

            $('#reset-btn').on('click', function () {
                $('#search-keyword').val('');
                $('#status').val('');
                if (table) table.ajax.reload();
            });
        },

        // Called from the inline onclick rendered by AdminCmsController::datatable()
        deletePage: function (id, url) {

            if (typeof Swal === 'undefined') return;

            Swal.fire({
                title: 'Are you sure?',
                text: 'This page will be deleted permanently.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {

                if (!result.isConfirmed) return;

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        id: id,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            if (table) table.ajax.reload();
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function () {
                        toastr.error('Something went wrong.');
                    }
                });
            });
        }
    };

})();


const CmsForm = (function () {

    var ckEditorInstance;
    var slugTimer;

    return {

        init: function () {
            var $form = $('#cmsForm');
            if (!$form.length) return;

            this.$form        = $form;
            this.saveLabel    = $form.data('save-label')   || 'Save';
            this.savingLabel  = $form.data('saving-label') || 'Saving...';
            this.redirectBack = $form.data('redirect-back') || '';
            this.initEditor();
            this.bindSlugPreview();
            this.bindSubmit();
        },

        initEditor: function () {
            
            if (typeof ClassicEditor === 'undefined') return;
            var el = document.querySelector('#description');
            if (!el) return;

            ClassicEditor.create(document.querySelector('#description'), {
                mediaEmbed: {
                    previewsInData: true
                },
                // ==============================
                // ✅ Image toolbar + resize
                // ==============================
                image: {
                    resizeOptions: [
                        {
                            name: 'resizeImage:original',
                            value: null,
                            label: 'Original size'
                        },
                        {
                            name: 'resizeImage:25',
                            value: '25',
                            label: '25%'
                        },
                        {
                            name: 'resizeImage:50',
                            value: '50',
                            label: '50%'
                        },
                        {
                            name: 'resizeImage:75',
                            value: '75',
                            label: '75%'
                        }
                    ],
                    styles: [
                        'full',
                        'side',
                        'alignLeft',
                        'alignCenter',
                        'alignRight'
                    ]
                }
            })
            .then(editor => {

                ckEditorInstance = editor;

                // ==============================
                // ✅ Image Upload (Base64)
                // ==============================
                editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                    return {
                        upload: () => {
                            return loader.file.then(file => {
                                return new Promise((resolve, reject) => {
                                    const reader = new FileReader();
                                    reader.onload = () => {
                                        resolve({
                                            default: reader.result
                                        });
                                    };
                                    reader.onerror = error => reject(error);
                                    reader.readAsDataURL(file);
                                });
                            });
                        },
                        abort: () => {}
                    };
                };

            })
            .catch(error => console.error(error));
        },

        bindSlugPreview: function () {

            var slugUrl = this.$form.data('slug-url');
            if (!slugUrl) return;

            var ignoreId = this.$form.data('page-id') || null;

            $('#title').on('input', function () {

                clearTimeout(slugTimer);

                var title = $(this).val().trim();
                if (!title) {
                    $('#slug-preview').val('');
                    return;
                }

                slugTimer = setTimeout(function () {

                    var params = { title: title };
                    if (ignoreId) params.ignore_id = ignoreId;

                    $.get(slugUrl, params, function (res) {
                        $('#slug-preview').val(res.slug);
                    });

                }, 400);
            });
        },

        bindSubmit: function () {

            var self = this;

            this.$form.on('submit', function (e) {
                e.preventDefault();
                self.submitAjax(this);
            });
        },

       submitAjax: function (form) {
            var self = this;
            var $form = $(form);

            this.clearErrors();

            var maxBytes = 1.5 * 1024 * 1024;
            var oversized = [];

            $form.find('input[type="file"]').each(function () {
                var input = this;
                if (input.files && input.files.length) {
                    for (var i = 0; i < input.files.length; i++) {
                        if (input.files[i].size > maxBytes) {
                            oversized.push({
                                name: input.files[i].name,
                                field: input.name || input.id || 'file'
                            });
                        }
                    }
                }
            });

            if (oversized.length) {
                var names = oversized.map(function (f) { return f.name; }).join(', ');
                toastr.error(
                    'These files exceed the 1.5 MB limit: ' + names
                );
                return;
            }

            if (typeof ckEditorInstance !== 'undefined' && ckEditorInstance) {
                document.querySelector('#description').value = ckEditorInstance.getData();
            }

            var formData = new FormData(form);

            var $btn = $('#submitBtn');

            $btn.prop('disabled', true).text(this.savingLabel);

            $.ajax({
                url: $form.data('action') || $form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                success: function (res) {
                    $btn.prop('disabled', false).text(self.saveLabel);
                    if (res.success) {
                        toastr.success(res.message || 'Saved Successfully');
                        setTimeout(function () {
                            window.location.href =
                                res.redirect_url || self.redirectBack;
                        }, 800);
                    } else {
                        toastr.error(res.message || 'Something went wrong.');
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).text(self.saveLabel);
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors || {};
                        if (typeof window.applyServerErrors === 'function') {
                            window.applyServerErrors($form, errors);
                        } else {
                            $.each(errors, function (field, msgs) {
                                var id = field.replace(/\./g, '_');
                                $('#error-' + id)
                                    .text(msgs[0])
                                    .removeClass('d-none');
                            });
                        }

                    } else {

                        toastr.error(
                            xhr.responseJSON?.message ||
                            'Something went wrong.'
                        );

                    }

                }

            });

        },

        clearErrors: function () {
            $('.form-valid-error').addClass('d-none').text('');
        }
    };

})();


// Globals — the controller-built row HTML calls these directly via inline onclick
window.deleteCmsPage = function (id, url) {
    CmsList.deletePage(id, url);
};


$(function () {
    CmsList.init();
    CmsForm.init();
});
