/*
 * Checklist Verification module
 *
 * Drives the Verify Checklist screen:
 *   - Accordion expand/collapse per category
 *   - Live badge + status update per item when checkbox changes
 *   - Live stat recalculation (total/completed/pending/percent)
 *   - Progress bar colour switch based on percentage
 *   - AJAX save — returns fresh stats from server to re-sync counters
 */

const ChecklistVerify = (function () {

    return {

        init: function () {
            var $form = $('#verifyForm');
            if (!$form.length) return;

            this.$form        = $form;
            this.saveLabel    = $form.data('save-label')   || 'Save Progress';
            this.savingLabel  = $form.data('saving-label') || 'Saving...';

            this.initAccordion();
            this.bindCheckboxes();
            this.bindSubmit();
        },

        // ----------------------------------------------------------------
        // Accordion
        // ----------------------------------------------------------------

        initAccordion: function () {
            $(document).on('click', '.verify-cat-header', function () {
                var catId  = $(this).closest('.verify-category-card').data('cat-id');
                var $body  = $('#vcat-' + catId);
                var $chev  = $('#vchevron-' + catId);

                $body.toggleClass('open');
                $chev.toggleClass('open');
            });
        },

        // ----------------------------------------------------------------
        // Checkbox — live badge + row highlight + counter update
        // ----------------------------------------------------------------

        bindCheckboxes: function () {
            $(document).on('change', '.verify-checkbox', function () {
                var pcId    = $(this).data('pc-id');
                var catId   = $(this).data('cat');
                var checked = $(this).prop('checked');
                var $row    = $('#row-' + pcId);
                var $badge  = $('#badge-' + pcId);

                // Row highlight
                $row.toggleClass('is-done', checked);

                // Status badge
                if (checked) {
                    $badge
                        .removeClass('bg-warning-subtle text-warning')
                        .addClass('bg-success-subtle text-success')
                        .html('<i class="fa fa-circle-check me-1"></i>Completed');
                } else {
                    $badge
                        .removeClass('bg-success-subtle text-success')
                        .addClass('bg-warning-subtle text-warning')
                        .html('<i class="fa fa-clock me-1"></i>Pending');

                    // Clear meta info when unchecked (will be confirmed on save)
                    $('#meta-' + pcId).addClass('d-none').html('');
                }

                // Category badge + mini bar
                ChecklistVerify.updateCategoryStats(catId);

                // Global counters
                ChecklistVerify.updateGlobalStats();
            });
        },

        // ----------------------------------------------------------------
        // Category-level badge and mini progress bar
        // ----------------------------------------------------------------

        updateCategoryStats: function (catId) {
            var $catItems = $('.verify-checkbox[data-cat="' + catId + '"]');
            var total     = $catItems.length;
            var done      = $catItems.filter(':checked').length;
            var pct       = total > 0 ? Math.round((done / total) * 100) : 0;

            $('#catbadge-' + catId).text(done + ' / ' + total);

            var $bar = $('#catbar-' + catId);
            $bar.css('width', pct + '%')
                .removeClass('bg-danger bg-warning bg-success')
                .addClass(pct < 40 ? 'bg-danger' : (pct < 75 ? 'bg-warning' : 'bg-success'));
        },

        // ----------------------------------------------------------------
        // Global counters and main progress bar
        // ----------------------------------------------------------------

        updateGlobalStats: function () {
            var total     = $('.verify-checkbox').length;
            var completed = $('.verify-checkbox:checked').length;
            var pending   = total - completed;
            var pct       = total > 0 ? Math.round((completed / total) * 100) : 0;

            this.applyStats({ total: total, completed: completed, pending: pending, percent: pct });
        },

        applyStats: function (stats) {
            $('#stat-total').text(stats.total);
            $('#stat-completed').text(stats.completed);
            $('#stat-pending').text(stats.pending);
            $('#stat-percent').text(stats.percent + '%');
            $('#progress-label').text(stats.percent + '%');

            var $bar = $('#progress-bar');
            $bar.css('width', stats.percent + '%')
                .attr('aria-valuenow', stats.percent)
                .removeClass('bg-danger bg-warning bg-success')
                .addClass(stats.percent < 40 ? 'bg-danger' : (stats.percent < 75 ? 'bg-warning' : 'bg-success'));
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
            var $btn  = $('#saveVerifyBtn');

            $btn.prop('disabled', true)
                .html('<i class="fa fa-spinner fa-spin me-1"></i>' + this.savingLabel);

            $.ajax({
                url:  this.$form.data('action'),
                type: 'POST',
                data: this.$form.serialize(),

                success: function (res) {
                    if (res.success) {
                        toastr.success(res.message);

                        // Sync stats from server response
                        if (res.stats) {
                            self.applyStats(res.stats);
                        }

                        // Refresh all category stats
                        $('.verify-category-card').each(function () {
                            ChecklistVerify.updateCategoryStats($(this).data('cat-id'));
                        });

                    } else {
                        toastr.error(res.message || 'Something went wrong.');
                    }
                },

                error: function () {
                    toastr.error('Something went wrong. Please try again.');
                },

                complete: function () {
                    $btn.prop('disabled', false)
                        .html('<i class="fa fa-floppy-disk me-1"></i>' + self.saveLabel);
                }
            });
        }
    };

})();


$(function () {
    ChecklistVerify.init();
});
