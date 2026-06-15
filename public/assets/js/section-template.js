/*
 * Section Template module
 *
 * Drives the Section Template admin screens:
 *   - List page  (table[data-section-template-list]) -> SectionTemplateList
 *
 * URLs and labels come from data-* attributes on the host elements,
 * so this file is static and contains no Blade output.
 */

const SectionTemplateList = (function () {

    var table;

    return {

        init: function () {

            var $table = $('table[data-section-template-list]');

            if (!$table.length) return;

            this.initTable($table);
            this.bindEvents();
        },

        initTable: function ($table) {

            table = $table.DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ordering: false,

                dom: "<'row'<'col-12'tr>>" +
                     "<'row datatable-footer'<'col-auto'l><'col-12 col-md'i><'col-12 col-md-auto'p>>",

                ajax: {
                    url: $table.data('url'),

                    data: function (d) {
                        d.search = $('#search-keyword').val();
                    }
                },

                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'key', name: 'key' },
                    { data: 'content_type', name: 'content_type' },
                    { data: 'status', orderable: false, searchable: false },
                    { data: 'actions', orderable: false, searchable: false }
                ]
            });
        },

        bindEvents: function () {

            var self = this;

            // Search
            $('#search-btn').on('click', function () {

                if (table) {
                    table.ajax.reload();
                }
            });

            // Reset
            $('#reset-btn').on('click', function () {

                $('#search-keyword').val('');

                if (table) {
                    table.ajax.reload();
                }
            });

            // Toggle Status
            $(document).on('click', '.toggle-status', function () {

                var id  = $(this).data('id');
                var url = $(this).data('url');

                self.toggleStatus(id, url);
            });

            // Delete Template
            $(document).on('click', '.delete-btn', function () {

                var id  = $(this).data('id');
                var url = $(this).data('url');

                self.deleteTemplate(id, url);
            });
        },

        /*
        |--------------------------------------------------------------------------
        | Toggle Status
        |--------------------------------------------------------------------------
        */

        toggleStatus: function (id,url) {
            
            $.ajax({
                url: url,
                type: 'POST',

                data: {
                    id: id,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },

                success: function (res) {

                    if (res.success || res.status === 'success') {

                        toastr.success(
                            res.message || 'Status updated successfully.'
                        );

                        if (table) {
                            table.ajax.reload(null, false);
                        }

                    } else {

                        toastr.error(
                            res.message || 'Something went wrong.'
                        );
                    }
                },

                error: function () {
                    toastr.error('Something went wrong.');
                }
            });
        },

        /*
        |--------------------------------------------------------------------------
        | Delete Template
        |--------------------------------------------------------------------------
        */

        deleteTemplate: function (id, url) {

            if (typeof Swal === 'undefined') return;

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you really want to delete this template?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'No, Cancel'

            }).then(function (result) {

                if (!result.isConfirmed) return;

                $.ajax({
                    url: url,
                    type: 'DELETE',

                    data: {
                        id: id,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },

                    success: function (res) {

                        if (res.success || res.status === 'success') {

                            toastr.success(
                                res.message || 'Template deleted successfully.'
                            );

                            if (table) {
                                table.ajax.reload(null, false);
                            }

                        } else {

                            toastr.error(
                                res.message || 'Something went wrong.'
                            );
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

const SectionTemplateForm = (function () {

    return {

        init: function () {

            var $form = $('#templateForm');

            if (!$form.length) return;

            this.$form        = $form;
            this.saveLabel    = 'Save Template';
            this.savingLabel  = 'Saving...';
            this.redirectBack = $form.data('redirect-back') || '';

            this.bindSubmit();
        },

        bindSubmit: function () {

            var self = this;

            this.$form.on('submit', function (e) {

                e.preventDefault();                
                self.submitAjax(this);
            });
        },

        submitAjax: function (form) {

            var self  = this;
            var $form = $(form);

            this.clearErrors();

            var $btn = $form.find('button[type="submit"]');

            $btn.prop('disabled', true)
                .text(this.savingLabel);

            $.ajax({

                url:  $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),

                success: function (res) {

                    if (res.success) {

                        toastr.success(
                            res.message || 'Template created successfully.'
                        );

                        setTimeout(function () {

                            window.location.href =
                                res.redirect_url || self.redirectBack;

                        }, 800);

                    } else {

                        $btn.prop('disabled', false)
                            .text(self.saveLabel);

                        toastr.error(
                            res.message || 'Something went wrong.'
                        );
                    }
                },

                error: function (xhr) {

                    $btn.prop('disabled', false)
                        .text(self.saveLabel);

                    if (xhr.status === 422) {

                        var errors = xhr.responseJSON.errors;

                        $.each(errors, function (field, msgs) {

                            var input = $('[name="' + field + '"]');

                            input.addClass('is-invalid');

                            input.closest('.mb-3')
                                .find('.invalid-feedback')
                                .remove();

                            input.after(
                                '<div class="invalid-feedback d-block">' +
                                    msgs[0] +
                                '</div>'
                            );
                        });

                    } else {

                        toastr.error('Something went wrong.');
                    }
                }
            });
        },

        clearErrors: function () {

            $('.is-invalid').removeClass('is-invalid');

            $('.invalid-feedback').remove();
        }
    };

})();


/*
|--------------------------------------------------------------------------
| Globals
|--------------------------------------------------------------------------
| Controller-built row HTML calls these directly via inline onclick
|
*/

window.deleteTemplate = function (id, url) {
    SectionTemplateList.deleteTemplate(id, url);
};

window.toggleTemplateStatus = function (id, url) {
    SectionTemplateList.toggleStatus(id, url);
};


/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/

$(function () {
    SectionTemplateList.init();
    SectionTemplateForm.init()
});