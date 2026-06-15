/*
 * FAQ module
 *
 * Drives the FAQ admin screens:
 *   - List page  (table[data-faq-list])  -> FaqList
 *   - Add/Edit   (form#faqForm)          -> FaqForm
 *
 * URLs and labels come from data-* attributes on the host elements,
 * so this file is static and contains no Blade output.
 */

const FaqList = (function () {

    var table;

    return {

        init: function () {
            var $table = $('table[data-faq-list]');
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
                        d.status  = $('#status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'question' },
                    { data: 'answer' },
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

        // Called from the inline onclick rendered by AdminFaqController::datatable()
        deleteFaq: function (id, url) {

            if (typeof Swal === 'undefined') return;

            Swal.fire({
                title: 'Are you sure?',
                text: 'This FAQ will be deleted permanently.',
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


const FaqForm = (function () {

    return {

        init: function () {
            var $form = $('#faqForm');
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

            var self = this;
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
                            $('#error-' + field).text(msgs[0]).removeClass('d-none');
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


// Globals — the controller-built row HTML calls these directly via inline onclick
window.deleteFaq = function (id, url) {
    FaqList.deleteFaq(id, url);
};


$(function () {
    FaqList.init();
    FaqForm.init();
});
