/*
 * Project module
 *
 * Drives the Project admin screens:
 *   - List page  (table[data-project-list])  -> ProjectList
 *   - Add/Edit   (form#projectForm)          -> ProjectForm
 */

const ProjectList = (function () {

    var table;

    return {

        init: function () {
            var $table = $('table[data-project-list]');
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
                dom: "<'row'<'col-12'tr>><'row datatable-footer'<'col-auto'l><'col-12 col-md' i><'col-12 col-md-auto'p>>",
                ajax: {
                    url: $table.data('url'),
                    data: function (d) {
                        d.keyword = $('#search-keyword').val();
                        d.status  = $('#filter-status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex',  orderable: false, searchable: false },
                    { data: 'project_name', orderable: false },
                    { data: 'client_name',  orderable: false },
                    { data: 'status',       orderable: false },
                    { data: 'created_at',   orderable: false },
                    { data: 'action',       orderable: false }
                ]
            });
        },

        bindEvents: function () {
            $('#search-btn').on('click', function () {
                if (table) table.ajax.reload();
            });

            $('#reset-btn').on('click', function () {
                $('#search-keyword').val('');
                $('#filter-status').val('');
                if (table) table.ajax.reload();
            });

            $('#search-keyword').on('keydown', function (e) {
                if (e.key === 'Enter' && table) table.ajax.reload();
            });
        },

        deleteProject: function (id, url) {
            if (typeof Swal === 'undefined') return;

            Swal.fire({
                title: 'Are you sure?',
                text: 'This project and all its assigned checklist records will be deleted permanently.',
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
        },

        copyShareUrl: function (btn) {
            var url = $(btn).data('url');

            if (!url) {
                toastr.warning('No share URL available for this project.');
                return;
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function () {
                    toastr.success('Share URL copied to clipboard!');
                }).catch(function () {
                    ProjectList._fallbackCopy(url);
                });
            } else {
                ProjectList._fallbackCopy(url);
            }
        },

        _fallbackCopy: function (text) {
            var $tmp = $('<input>').val(text).appendTo('body').select();
            try {
                document.execCommand('copy');
                toastr.success('Share URL copied to clipboard!');
            } catch (e) {
                toastr.error('Could not copy URL. Please copy manually: ' + text);
            }
            $tmp.remove();
        }
    };

})();


const ProjectForm = (function () {

    return {

        init: function () {
            var $form = $('#projectForm');
            if (!$form.length) return;

            this.$form        = $form;
            this.saveLabel    = $form.data('save-label')   || 'Save';
            this.savingLabel  = $form.data('saving-label') || 'Saving...';
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

            var $btn = $('#submitBtn');
            $btn.prop('disabled', true).text(this.savingLabel);

            $.ajax({
                url:  $form.data('action') || $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),

                success: function (res) {
                    if (res.success) {
                        toastr.success(res.message);
                        setTimeout(function () {
                            window.location.href = res.redirect_url || self.redirectBack;
                        }, 800);
                    } else {
                        $btn.prop('disabled', false).text(self.saveLabel);
                        toastr.error(res.message || 'Something went wrong.');
                    }
                },

                error: function (xhr) {
                    $btn.prop('disabled', false).text(self.saveLabel);

                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function (field, msgs) {
                            var key = field.replace(/\[.*\]/, '');
                            $('#error-' + key).text(msgs[0]).removeClass('d-none');
                        });
                    } else {
                        toastr.error('Something went wrong.');
                    }
                }
            });
        },

        clearErrors: function () {
            $('.form-valid-error').addClass('d-none').text('');
        }
    };

})();


// Globals — called from inline onclick attributes in the datatable rows
window.ProjectList = ProjectList;


$(function () {
    ProjectList.init();
    ProjectForm.init();
});
