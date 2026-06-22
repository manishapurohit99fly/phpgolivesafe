/*
 * Checklist Verification module
 *
 * Drives the Verify Checklist screen:
 *   - Accordion expand/collapse per category
 *   - Live badge + status update per item when checkbox changes
 *   - Live stat recalculation (total/completed/pending/percent)
 *   - Progress bar colour switch based on percentage
 *   - AJAX save — returns fresh stats from server to re-sync counters
 *   - Sequential mode (data-sequential="1"): locks items until previous is done
 */

const ChecklistVerify = (function () {

    return {

        init: function () {
            var $form = $('#verifyForm');
            if (!$form.length) return;

            this.$form       = $form;
            this.sequential  = !!$form.data('sequential');
            this.saveLabel   = $form.data('save-label')   || 'Save Progress';
            this.savingLabel = $form.data('saving-label') || 'Saving...';

            this.initAccordion();
            this.bindCheckboxes();
            this.bindSubmit();

            if (this.sequential) {
                this.reevaluateLocks();
            }
        },

        // ----------------------------------------------------------------
        // Accordion
        // ----------------------------------------------------------------

        initAccordion: function () {
            $(document).on('click', '.verify-cat-header', function () {
                var catId = $(this).closest('.verify-category-card').data('cat-id');
                $('#vcat-' + catId).toggleClass('open');
                $('#vchevron-' + catId).toggleClass('open');
            });
        },

        // ----------------------------------------------------------------
        // Checkbox — live badge + row highlight + counters
        // ----------------------------------------------------------------

        bindCheckboxes: function () {
            var self = this;

            $(document).on('change', '.verify-checkbox', function () {
                var $cb    = $(this);
                var pcId   = $cb.data('pc-id');
                var catId  = $cb.data('cat');
                var checked = $cb.prop('checked');

                // Sequential: unchecking an item resets all items after it
                if (self.sequential && !checked) {
                    self.cascadeUncheck($cb);
                }

                self.updateItemUI(pcId, checked);

                if (self.sequential) {
                    self.reevaluateLocks();
                    // Re-calc ALL category bars (cascade may have touched other categories)
                    $('.verify-category-card').each(function () {
                        ChecklistVerify.updateCategoryStats($(this).data('cat-id'));
                    });
                } else {
                    ChecklistVerify.updateCategoryStats(catId);
                }

                ChecklistVerify.updateGlobalStats();
            });
        },

        // ----------------------------------------------------------------
        // Sequential helpers
        // ----------------------------------------------------------------

        cascadeUncheck: function ($cb) {
            var $all = $('.verify-checkbox');
            var idx  = $all.index($cb);

            // Uncheck and update UI for every checkbox after the current one
            $all.slice(idx + 1).each(function () {
                if ($(this).prop('checked')) {
                    $(this).prop('checked', false);
                    ChecklistVerify.updateItemUI($(this).data('pc-id'), false);
                }
            });
        },

        reevaluateLocks: function () {
            var prevChecked = true;

            $('.verify-checkbox').each(function () {
                var $cb   = $(this);
                var pcId  = $cb.data('pc-id');
                var $row  = $('#row-' + pcId);
                var isChecked = $cb.prop('checked');

                if (!isChecked && !prevChecked) {
                    // Lock
                    $cb.prop('disabled', true);
                    $row.addClass('is-locked').removeClass('is-done');
                    $('#badge-' + pcId)
                        .removeClass('bg-warning-subtle text-warning bg-success-subtle text-success')
                        .addClass('bg-secondary-subtle text-secondary')
                        .html('<i class="fa fa-lock me-1"></i>Locked');
                    var $meta = $('#meta-' + pcId);
                    $meta.removeClass('d-none').html('<i class="fa fa-circle-info me-1"></i>Complete the previous item to unlock this one.');
                } else {
                    // Unlock
                    $cb.prop('disabled', false);
                    $row.removeClass('is-locked');
                    // Only fix badge/meta if item is unchecked (checked items were handled by updateItemUI)
                    if (!isChecked) {
                        var $badge = $('#badge-' + pcId);
                        if ($badge.find('.fa-lock').length) {
                            $badge
                                .removeClass('bg-secondary-subtle text-secondary')
                                .addClass('bg-warning-subtle text-warning')
                                .html('<i class="fa fa-clock me-1"></i>Pending');
                            $('#meta-' + pcId).addClass('d-none').html('');
                        }
                    }
                }

                prevChecked = isChecked;
            });
        },

        // ----------------------------------------------------------------
        // Item UI helpers
        // ----------------------------------------------------------------

        updateItemUI: function (pcId, checked) {
            var $row   = $('#row-' + pcId);
            var $badge = $('#badge-' + pcId);

            $row.toggleClass('is-done', checked);

            if (checked) {
                $badge
                    .removeClass('bg-warning-subtle text-warning bg-secondary-subtle text-secondary')
                    .addClass('bg-success-subtle text-success')
                    .html('<i class="fa fa-circle-check me-1"></i>Completed');
            } else {
                $badge
                    .removeClass('bg-success-subtle text-success bg-secondary-subtle text-secondary')
                    .addClass('bg-warning-subtle text-warning')
                    .html('<i class="fa fa-clock me-1"></i>Pending');
                $('#meta-' + pcId).addClass('d-none').html('');
            }
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

            $('#catbar-' + catId)
                .css('width', pct + '%')
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

            $('#progress-bar')
                .css('width', stats.percent + '%')
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
            var self = this;
            var $btn = $('#saveVerifyBtn');

            $btn.prop('disabled', true)
                .html('<i class="fa fa-spinner fa-spin me-1"></i>' + this.savingLabel);

            $.ajax({
                url:  this.$form.data('action'),
                type: 'POST',
                data: this.$form.serialize(),

                success: function (res) {
                    if (res.success) {
                        toastr.success(res.message);
                        if (res.stats) {
                            self.applyStats(res.stats);
                        }
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
