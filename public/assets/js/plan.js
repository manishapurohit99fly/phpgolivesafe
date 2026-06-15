
const PlanList = (function () {

    let table;

    return {

        init: function () {

            let $table = $('table[data-plan-list]');

            if (!$table.length) return;

            this.initTable($table);

            this.bindEvents();
        },

        /*
        |--------------------------------------------------------------------------
        | Datatable
        |--------------------------------------------------------------------------
        */

        initTable: function ($table) {

            table = $table.DataTable({

                processing: true,

                serverSide: true,

                searching: false,

                ordering: false,

                responsive: true,

                dom: `
                    <'row'<'col-12'tr>>
                    <'row datatable-footer'
                        <'col-auto'l>
                        <'col-12 col-md'i>
                        <'col-12 col-md-auto'p>
                    >
                `,

                ajax: {

                    url: $table.data('url'),

                    data: function (d) {

                        d.keyword = $('#search-keyword').val();

                        d.status = $('#status').val();
                    }
                },

                columns: [

                    {
                        data: 'DT_RowIndex',
                        searchable: false,
                        orderable: false
                    },

                    {
                        data: 'name'
                    },

                    {
                        data: 'price'
                    },

                    {
                        data: 'interval'
                    },

                    {
                        data: 'trial_days'
                    },

                    {
                        data: 'features'
                    },

                    {
                        data: 'popular',
                        searchable: false,
                        orderable: false
                    },

                    {
                        data: 'sort_order'
                    },

                    {
                        data: 'status',
                        searchable: false,
                        orderable: false
                    },

                    {
                        data: 'action',
                        searchable: false,
                        orderable: false
                    }
                ]
            });
        },

        /*
        |--------------------------------------------------------------------------
        | Events
        |--------------------------------------------------------------------------
        */

        bindEvents: function () {

            $('#search-btn').on('click', function () {

                if (table) {

                    table.ajax.reload();
                }
            });

            $('#reset-btn').on('click', function () {

                $('#search-keyword').val('');

                $('#status').val('');

                if (table) {

                    table.ajax.reload();
                }
            });
        },

        /*
        |--------------------------------------------------------------------------
        | Delete Plan
        |--------------------------------------------------------------------------
        */

        deletePlan: function (id, url) {

            Swal.fire({

                title: 'Delete Plan?',

                text: 'This action cannot be undone.',

                icon: 'warning',

                showCancelButton: true,

                confirmButtonText: 'Yes, Delete',

                cancelButtonText: 'Cancel',

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

                        if (res.success) {

                            toastr.success(
                                res.message || 'Plan deleted successfully.'
                            );

                            if (table) {

                                table.ajax.reload();
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


/*
|--------------------------------------------------------------------------
| Global Delete Function
|--------------------------------------------------------------------------
*/

window.deletePlan = function (id, url) {

    PlanList.deletePlan(id, url);
};



/*
|--------------------------------------------------------------------------
| Plan Form Module
|--------------------------------------------------------------------------
*/

const PlanForm = (function () {

    let featureIndex = 0;

    return {

        init: function () {

            let $form = $('#planForm');

            if (!$form.length) return;

            this.$form        = $form;
            this.saveLabel    = 'Save Plan';
            this.savingLabel  = 'Saving...';
            this.redirectBack = $form.data('redirect-back') || '';

            featureIndex = $('.feature-row').length;

            this.bindFeatureEvents();
            this.initValidation();
        },

        /*
        |--------------------------------------------------------------------------
        | Feature Events
        |--------------------------------------------------------------------------
        */

        bindFeatureEvents: function () {

            let self = this;

            // Add Feature
            $('#addFeatureBtn').on('click', function () {

                self.addFeatureRow();
            });

            // Remove Feature
            $(document).on('click', '.remove-feature', function () {

                $(this).closest('.feature-row').remove();
            });
        },

        addFeatureRow: function () {

            let html = `
                <div class="feature-row d-flex align-items-start gap-2 mb-2">

                    <div class="form-check mt-2">
                        <input 
                            type="checkbox"
                            class="form-check-input"
                            name="features[${featureIndex}][included]"
                            value="1"
                            checked
                        >
                    </div>

                    <div class="flex-grow-1 feature-input-wrapper">

                        <input 
                            type="text"
                            name="features[${featureIndex}][feature]"
                            class="form-control feature-input"
                            placeholder="Feature text"
                            maxlength="255"
                        >

                    </div>

                    <button 
                        type="button"
                        class="btn btn-sm btn-outline-danger remove-feature"
                    >
                        <i class="fa fa-trash"></i>
                    </button>

                </div>
            `;

            $('#featuresContainer').append(html);

            let $input = $('input[name="features[' + featureIndex + '][feature]"]');

            // Dynamic validation
            $input.rules('add', {

                required: true,

                messages: {
                    required: 'Feature field is required.'
                }
            });

            featureIndex++;
        },

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        initValidation: function () {

            let self = this;

            this.$form.validate({

                ignore: [],

                rules: {

                    name: {
                        required: true,
                        maxlength: 255
                    },

                    price: {
                        required: true,
                        number: true,
                        min: 0
                    },

                    interval: {
                        required: true
                    },

                    description: {
                        maxlength: 1000
                    },

                    button_text: {
                        maxlength: 100
                    },

                    badge_text: {
                        maxlength: 100
                    },

                    trial_days: {
                        number: true,
                        min: 0,
                        max: 365
                    },

                    sort_order: {
                        number: true,
                        min: 0
                    }
                },

                messages: {

                    name: {
                        required: 'Plan name is required.'
                    },

                    price: {
                        required: 'Price is required.',
                        number: 'Enter valid price.',
                        min: 'Price must be greater than or equal to 0.'
                    },

                    interval: {
                        required: 'Billing interval is required.'
                    }
                },

                errorElement: 'div',

                errorClass: 'invalid-feedback',

                highlight: function (element) {

                    $(element).addClass('is-invalid');
                },

                unhighlight: function (element) {

                    $(element).removeClass('is-invalid');
                },

                errorPlacement: function (error, element) {

                    error.addClass('d-block');

                    // Feature input error below input
                    if (element.hasClass('feature-input')) {

                        error.appendTo(
                            element.closest('.feature-input-wrapper')
                        );

                    }
                    // Input group fields
                    else if (element.closest('.input-group').length) {

                        error.insertAfter(
                            element.closest('.input-group')
                        );

                    }
                    // Normal fields
                    else {

                        error.insertAfter(element);
                    }
                },

                submitHandler: function (form) {

                    self.submitAjax(form);
                }
            });

            /*
            |--------------------------------------------------------------------------
            | Existing Feature Validation
            |--------------------------------------------------------------------------
            */

            $('.feature-input').each(function () {

                $(this).rules('add', {

                    required: true,

                    messages: {
                        required: 'Feature field is required.'
                    }
                });
            });
        },

        /*
        |--------------------------------------------------------------------------
        | AJAX Submit
        |--------------------------------------------------------------------------
        */

        submitAjax: function (form) {

            let self  = this;
            let $form = $(form);

            self.clearErrors();

            let $btn = $form.find('button[type="submit"]');

            $btn.prop('disabled', true)
                .text(this.savingLabel);
            
            $.ajax({

                url: $form.attr('action'),

                type: 'POST',

                data: $form.serialize(),

                success: function (res) {
                    
                    $btn.prop('disabled', false)
                        .text(self.saveLabel);

                    if (res.success) {

                        toastr.success(
                            res.message || 'Plan created successfully.'
                        );

                        setTimeout(function () {

                            window.location.href =
                                res.redirect_url || self.redirectBack;

                        }, 800);

                    } else {

                        toastr.error(
                            res.message || 'Something went wrong.'
                        );
                    }
                },

                error: function (xhr) {

                    $btn.prop('disabled', false)
                        .text(self.saveLabel);

                    self.clearErrors();

                    if (xhr.status === 422) {

                        let errors = xhr.responseJSON.errors;

                        $.each(errors, function (field, msgs) {

                            let inputName = field;

                            /*
                            |--------------------------------------------------------------------------
                            | Convert dot notation
                            | features.0.feature => features[0][feature]
                            |--------------------------------------------------------------------------
                            */

                            if (field.includes('.')) {

                                let parts = field.split('.');

                                inputName = parts[0];

                                for (let i = 1; i < parts.length; i++) {

                                    inputName += '[' + parts[i] + ']';
                                }
                            }

                            let $input = $('[name="' + inputName + '"]');

                            if (!$input.length) return;

                            $input.addClass('is-invalid');

                            /*
                            |--------------------------------------------------------------------------
                            | Remove old error
                            |--------------------------------------------------------------------------
                            */

                            if ($input.hasClass('feature-input')) {

                                $input.closest('.feature-input-wrapper')
                                    .find('.invalid-feedback')
                                    .remove();

                            } else {

                                $input.next('.invalid-feedback').remove();
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Append Error
                            |--------------------------------------------------------------------------
                            */

                            if ($input.hasClass('feature-input')) {

                                $input.closest('.feature-input-wrapper')
                                    .append(`
                                        <div class="invalid-feedback d-block">
                                            ${msgs[0]}
                                        </div>
                                    `);

                            } else {

                                $input.after(`
                                    <div class="invalid-feedback d-block">
                                        ${msgs[0]}
                                    </div>
                                `);
                            }
                        });

                    } else {

                        toastr.error('Something went wrong.');
                    }
                }
            });
        },

        /*
        |--------------------------------------------------------------------------
        | Clear Errors
        |--------------------------------------------------------------------------
        */

        clearErrors: function () {

            $('.is-invalid').removeClass('is-invalid');

            $('.invalid-feedback').remove();
        }

    };

})();


/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/

$(function () {
    PlanList.init();
    PlanForm.init();
});