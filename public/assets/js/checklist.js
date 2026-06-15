/*
 * Checklist Assignment module
 *
 * Drives the Assign Checklist screen:
 *   - Accordion expand/collapse per category
 *   - Per-category select all / indeterminate state
 *   - Global Select All / Deselect All
 *   - Live counter
 *   - AJAX save
 */

const ChecklistAssign = (function () {

    return {

        init: function () {
            var $form = $('#checklistForm');
            if (!$form.length) return;

            this.$form        = $form;
            this.saveLabel    = $form.data('save-label')   || 'Save Assignment';
            this.savingLabel  = $form.data('saving-label') || 'Saving...';
            
            this.initCategoryCheckboxes();
            this.initItemCheckboxes();
            this.bindGlobalButtons();
            this.bindSubmit();

            // Restore indeterminate states on load
            this.refreshAllCategoryStates();
            this.updateCounter();
        },


        // ----------------------------------------------------------------
        // Category "select all" checkbox
        // ----------------------------------------------------------------

        initCategoryCheckboxes: function () {
            $(document).on('change', '.cat-select-all', function () {
                var catId    = $(this).data('cat');
                var checked  = $(this).prop('checked');

                $('#cat-' + catId).find('.item-checkbox').prop('checked', checked);

                ChecklistAssign.updateCategoryBadge(catId);
                ChecklistAssign.updateCounter();
            });
        },

        // ----------------------------------------------------------------
        // Individual item checkboxes
        // ----------------------------------------------------------------

        initItemCheckboxes: function () {
            $(document).on('change', '.item-checkbox', function () {
                var catId = $(this).data('cat');
                ChecklistAssign.refreshCategoryState(catId);
                ChecklistAssign.updateCounter();
            });
        },

        // ----------------------------------------------------------------
        // Refresh category checkbox state (checked / indeterminate / unchecked)
        // ----------------------------------------------------------------

        refreshCategoryState: function (catId) {
            var $items   = $('#cat-' + catId).find('.item-checkbox');
            var total    = $items.length;
            var checked  = $items.filter(':checked').length;
            var $catCb   = $('#cat_all_' + catId);

            if (checked === 0) {
                $catCb.prop('checked', false).prop('indeterminate', false);
            } else if (checked === total) {
                $catCb.prop('checked', true).prop('indeterminate', false);
            } else {
                $catCb.prop('checked', false).prop('indeterminate', true);
            }

            this.updateCategoryBadge(catId);
        },

        refreshAllCategoryStates: function () {
            var self = this;
            $('.cat-select-all').each(function () {
                self.refreshCategoryState($(this).data('cat'));
            });
        },

        updateCategoryBadge: function (catId) {
            var $items   = $('#cat-' + catId).find('.item-checkbox');
            var total    = $items.length;
            var checked  = $items.filter(':checked').length;
            $('#badge-' + catId).text(checked + ' / ' + total);
        },

        // ----------------------------------------------------------------
        // Counter
        // ----------------------------------------------------------------

        updateCounter: function () {
            var checked = $('.item-checkbox:checked').length;
            $('#selectedCount').text(checked);
        },

        // ----------------------------------------------------------------
        // Global buttons
        // ----------------------------------------------------------------

        bindGlobalButtons: function () {
            var self = this;

            $('#selectAllBtn').on('click', function () {
                $('.item-checkbox').prop('checked', true);
                self.refreshAllCategoryStates();
                self.updateCounter();
            });

            $('#deselectAllBtn').on('click', function () {
                $('.item-checkbox').prop('checked', false);
                self.refreshAllCategoryStates();
                self.updateCounter();
            });
        },

        // ----------------------------------------------------------------
        // AJAX Submit
        // ----------------------------------------------------------------

        bindSubmit: function () {
            var self = this;
            this.$form.on('submit', function (e) {
                e.preventDefault();
                self.save();
            });
        },

        save: function () {
            var self  = this;
            var $form = this.$form;

            var $btns = $('#saveBtn, #saveBtnBottom');
            $btns.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>' + this.savingLabel);

            $.ajax({
                url:  $form.data('action'),
                type: 'POST',
                data: $form.serialize(),

                success: function (res) {
                    if (res.success) {
                        toastr.success(res.message);
                        setTimeout(function () {
                            window.location.href = $form.data('redirect');
                        }, 1000);
                    } else {
                        toastr.error(res.message || 'Something went wrong.');
                        $btns.prop('disabled', false).html('<i class="fa fa-floppy-disk me-1"></i>' + self.saveLabel);
                    }
                },

                error: function () {
                    toastr.error('Something went wrong. Please try again.');
                    $btns.prop('disabled', false).html('<i class="fa fa-floppy-disk me-1"></i>' + self.saveLabel);
                },
            });
        }
    };

})();


$(function () {
    ChecklistAssign.init();
});
