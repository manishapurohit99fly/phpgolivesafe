/*
 * Project module
 *
 * Drives the Project admin screens:
 *   - List page  (accordion-based project listing)  -> ProjectList, AssessmentList
 *   - Add/Edit   (form#projectForm)                 -> ProjectForm
 *   - Assessment modal (form#assessmentForm)         -> AssessmentForm
 */

// ------------------------------------------------------------------
// ProjectList — accordion-based project listing with pagination
// ------------------------------------------------------------------

const ProjectList = (function () {

    var currentPage = 1;

    // ── Build pagination HTML (mirrors DataTable datatable-footer) ───
    function buildPagination(res) {
        var total    = res.total    || 0;
        var lastPage = res.last_page || 1;
        var page     = res.page     || 1;
        var perPage  = res.per_page || 10;
        var $pag     = $('#projectPagination');

        if (!$pag.length || total <= perPage) {
            $pag.addClass('d-none');
            return;
        }

        var from = total === 0 ? 0 : (page - 1) * perPage + 1;
        var to   = Math.min(page * perPage, total);

        $('#pagFrom').text(from);
        $('#pagTo').text(to);
        $('#pagTotal').text(total);
        $pag.removeClass('d-none');

        var html  = '';
        var start = Math.max(1, page - 2);
        var end   = Math.min(lastPage, page + 2);

        html += '<li class="paginate_button page-item previous' + (page <= 1 ? ' disabled' : '') + '">' +
                '<a class="page-link" href="#" data-page="' + (page - 1) + '">Previous</a></li>';

        if (start > 1) {
            html += '<li class="paginate_button page-item"><a class="page-link" href="#" data-page="1">1</a></li>';
            if (start > 2) html += '<li class="paginate_button page-item disabled"><a class="page-link">&hellip;</a></li>';
        }

        for (var i = start; i <= end; i++) {
            html += '<li class="paginate_button page-item' + (i === page ? ' active current' : '') + '">' +
                    '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
        }

        if (end < lastPage) {
            if (end < lastPage - 1) html += '<li class="paginate_button page-item disabled"><a class="page-link">&hellip;</a></li>';
            html += '<li class="paginate_button page-item"><a class="page-link" href="#" data-page="' + lastPage + '">' + lastPage + '</a></li>';
        }

        html += '<li class="paginate_button page-item next' + (page >= lastPage ? ' disabled' : '') + '">' +
                '<a class="page-link" href="#" data-page="' + (page + 1) + '">Next</a></li>';

        $('#projectPaginationList').html(html);
    }

    return {

        init: function () {
            if (!$('#projectAccordion').length) return;

            this.bindSearch();
            this.bindPagination();
            this.loadProjects(1);
            this.bindAccordion();
            AssessmentForm.init();
        },

        loadProjects: function (page) {
            currentPage = parseInt(page, 10) || 1;

            var $accordion = $('#projectAccordion');
            var $wrapper   = $accordion.closest('.dataTables_wrapper');
            var hasContent = $accordion.find('.project-accordion-item').length > 0;

            $wrapper.find('.dataTables_processing').remove();

            if (hasContent) {
                // Pagination click — overlay on top of existing rows (matches Manage Users behavior)
                $wrapper.css('position', 'relative')
                        .append('<div class="dataTables_processing">Loading...</div>');
            } else {
                // Initial / search load — replace empty area with spinner
                $accordion.html(
                    '<div class="text-center py-5 text-muted">' +
                    '<div class="spinner-border text-secondary mb-3" role="status" style="width:1.75rem;height:1.75rem;">' +
                    '<span class="visually-hidden">Loading...</span></div>' +
                    '<p class="mb-0 small">Loading projects...</p></div>'
                );
                $('#projectPagination').addClass('d-none');
            }

            $('#projectEmpty').addClass('d-none');

            $.get(window.projectListUrl, {
                keyword: $('#search-keyword').val(),
                status:  $('#filter-status').val(),
                page:    currentPage,
            }, function (res) {
                $wrapper.find('.dataTables_processing').remove();
                var total = res.total || 0;

                if (!res.html || res.count === 0) {
                    $accordion.empty();
                    $('#projectEmpty').removeClass('d-none');
                    $('#projectPagination').addClass('d-none');
                    $('#projectCountBadge').text('0 projects').show();
                } else {
                    $accordion.html(res.html);
                    buildPagination(res);
                    $('#projectCountBadge')
                        .text(total + (total === 1 ? ' project' : ' projects'))
                        .show();
                }
            }).fail(function () {
                $wrapper.find('.dataTables_processing').remove();
                $accordion.html(
                    '<div class="text-center py-4 text-danger small">' +
                    '<i class="fa fa-triangle-exclamation me-1"></i>Failed to load projects.</div>'
                );
                if (typeof toastr !== 'undefined') toastr.error('Failed to load projects.');
            });
        },

        bindSearch: function () {
            var self = this;

            $('#search-btn').on('click', function () {
                self.loadProjects(1);
            });

            $('#reset-btn').on('click', function () {
                $('#search-keyword').val('');
                $('#filter-status').val('').trigger('change');
                self.loadProjects(1);
            });

            $('#search-keyword').on('keydown', function (e) {
                if (e.key === 'Enter') self.loadProjects(1);
            });
        },

        bindPagination: function () {
            var self = this;
            $(document).on('click', '#projectPaginationList .page-link', function (e) {
                e.preventDefault();
                var $li = $(this).parent();
                if ($li.hasClass('disabled') || $li.hasClass('active')) return;
                var p = parseInt($(this).data('page'), 10);
                if (!p) return;
                self.loadProjects(p);
                // Scroll to top of list
                var $top = $('#projectAccordion').closest('.card');
                if ($top.length) {
                    $('html,body').animate({ scrollTop: $top.offset().top - 20 }, 200);
                }
            });
        },

        bindAccordion: function () {
            // Toggle project panel (delegated — rows are AJAX-rendered)
            $(document).on('click', '.toggle-project-btn', function () {
                var projectId = $(this).data('project-id');
                var $icon     = $(this).find('.toggle-icon');
                var $panel    = $('#assessment-panel-' + projectId);
                var $list     = $('#assessment-list-' + projectId);

                $panel.toggleClass('d-none');
                $icon.toggleClass('open');

                // Lazy-load assessments on first expand
                if (!$panel.hasClass('d-none') && $list.data('loaded') === false) {
                    AssessmentList.load(projectId);
                }
            });

            // Add Assessment button
            $(document).on('click', '.add-assessment-btn', function () {
                var projectId   = $(this).data('project-id');
                var projectName = $(this).data('project-name');
                AssessmentForm.open(projectId, projectName);
            });
        },

        deleteProject: function (encId, url) {
            if (typeof Swal === 'undefined') return;

            Swal.fire({
                title: 'Are you sure?',
                text: 'This project and all its data will be deleted permanently.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        id: encId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            ProjectList.loadProjects(currentPage);
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
            if (!url) { toastr.warning('No share URL available.'); return; }
            ProjectList._copy(url);
        },

        _copy: function (text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text)
                    .then(function () { toastr.success('Share URL copied to clipboard!'); })
                    .catch(function () { ProjectList._fallbackCopy(text); });
            } else {
                ProjectList._fallbackCopy(text);
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


// ------------------------------------------------------------------
// AssessmentList — lazy-load and manage assessments per project
// ------------------------------------------------------------------

const AssessmentList = (function () {

    return {

        load: function (projectId) {
            var $list = $('#assessment-list-' + projectId);
            $list.data('loaded', true);

            $.get(window.forProjectBaseUrl + '/' + projectId, function (res) {
                $list.html(res.html);
            }).fail(function () {
                $list.html('<div class="text-center py-3 text-danger small">Failed to load assessments.</div>');
            });
        },

        reload: function (projectId) {
            var $list = $('#assessment-list-' + projectId);
            $list.data('loaded', true);

            $.get(window.forProjectBaseUrl + '/' + projectId, function (res) {
                $list.html(res.html);
            });
        },

        deleteAssessment: function (encId, url, projectId) {
            if (typeof Swal === 'undefined') return;

            Swal.fire({
                title: 'Delete Assessment?',
                text: 'This assessment and all its checklist records will be deleted permanently.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        id: encId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            AssessmentList.reload(projectId);
                            ProjectList.loadProjects(1);
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
            var $btn     = $(btn);
            var encId    = $btn.data('assessment-id');
            var shareUrl = $btn.data('share-url');

            $.ajax({
                url:  shareUrl,
                type: 'POST',
                data: {
                    id: encId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    if (res.status === 'success') {
                        ProjectList._copy(res.share_url);
                    } else {
                        toastr.error(res.message || 'Could not get share URL.');
                    }
                },
                error: function () {
                    toastr.error('Something went wrong.');
                }
            });
        }
    };

})();


// ------------------------------------------------------------------
// AssessmentForm — Add Assessment modal
// ------------------------------------------------------------------

const AssessmentForm = (function () {

    var $modal, $form, currentProjectId;

    return {

        init: function () {
            $modal = $('#addAssessmentModal');
            $form  = $('#assessmentForm');
            if (!$modal.length || !$form.length) return;

            this.bindSubmit();

            $modal.on('hidden.bs.modal', function () {
                $form[0].reset();
                $form.find('.invalid-feedback').text('').hide();
                $form.find('.is-invalid').removeClass('is-invalid');
                $('#assessmentSubmitBtn')
                    .prop('disabled', false)
                    .html('<i class="fa fa-plus me-1"></i>Create Assessment');
                var $sel = $('#assessmentAssignedUser');
                if ($sel.hasClass('select2-hidden-accessible')) {
                    $sel.select2('destroy');
                }
                $sel.empty().append('<option value=""></option>');
            });
        },

        open: function (projectId, projectName) {
            currentProjectId = projectId;
            $('#assessmentProjectId').val(projectId);
            $('#modalProjectName').text(projectName);

            var $userSelect = $('#assessmentAssignedUser');

            // Tear down any previous Select2 instance before touching the DOM
            if ($userSelect.hasClass('select2-hidden-accessible')) {
                $userSelect.select2('destroy');
            }
            $userSelect.empty().append('<option value="">Loading users...</option>').prop('disabled', true);

            $modal.modal('show');

            // Fetch the project's assigned users, then init Select2 on the populated list
            $.get(window.projectUsersBaseUrl + '/' + projectId + '/users', function (res) {
                $userSelect.empty().append('<option value=""></option>');

                if (res.users && res.users.length) {
                    $.each(res.users, function (i, u) {
                        $userSelect.append(
                            $('<option>').val(u.id).text(u.name + ' (' + u.email + ')')
                        );
                    });
                } else {
                    $userSelect.append('<option value="" disabled>No users assigned to this project</option>');
                }

                // Init Select2 after options are in the DOM
                $userSelect.prop('disabled', false).select2({
                    placeholder: '— Select a verifier —',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $modal
                });
            }).fail(function () {
                $userSelect.empty()
                    .append('<option value="" disabled>Failed to load users</option>')
                    .prop('disabled', false);
            });
        },

        bindSubmit: function () {
            $form.on('submit', function (e) {
                e.preventDefault();
                AssessmentForm.submit();
            });
        },

        submit: function () {
            var $btn = $('#assessmentSubmitBtn');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Creating...');
            $form.find('.invalid-feedback').text('').hide();
            $form.find('.is-invalid').removeClass('is-invalid');

            $.ajax({
                url:  $form.data('store-url'),
                type: 'POST',
                data: $form.serialize(),

                success: function (res) {
                    if (res.success) {
                        toastr.success(res.message || 'Assessment created.');
                        $modal.modal('hide');

                        var pid = res.project_id;
                        var $panel = $('#assessment-panel-' + pid);
                        if (!$panel.hasClass('d-none')) {
                            AssessmentList.reload(pid);
                        }

                        ProjectList.loadProjects(1);

                        setTimeout(function () {
                            var $toggleBtn = $('.toggle-project-btn[data-project-id="' + pid + '"]');
                            var $panel2    = $('#assessment-panel-' + pid);
                            var $list      = $('#assessment-list-' + pid);
                            if ($panel2.hasClass('d-none')) {
                                $toggleBtn.find('.toggle-icon').addClass('open');
                                $panel2.removeClass('d-none');
                                if ($list.data('loaded') === false) {
                                    AssessmentList.load(pid);
                                } else {
                                    AssessmentList.reload(pid);
                                }
                            }
                        }, 500);
                    } else {
                        $btn.prop('disabled', false).html('<i class="fa fa-plus me-1"></i>Create Assessment');
                        toastr.error(res.message || 'Something went wrong.');
                    }
                },

                error: function (xhr) {
                    $btn.prop('disabled', false).html('<i class="fa fa-plus me-1"></i>Create Assessment');
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function (field, msgs) {
                            var key  = field.replace(/\[.*\]/, '');
                            var $err = $('#err-' + key);
                            if ($err.length) { $err.text(msgs[0]).show(); }
                            else { toastr.error(msgs[0]); }
                        });
                    } else {
                        toastr.error('Something went wrong.');
                    }
                }
            });
        }
    };

})();


// ------------------------------------------------------------------
// ProjectForm — Add / Edit project page
// ------------------------------------------------------------------

const ProjectForm = (function () {

    return {

        init: function () {
            var $form = $('#projectForm');
            if (!$form.length) return;

            this.$form        = $form;
            this.saveLabel    = $form.data('save-label')    || 'Save';
            this.savingLabel  = $form.data('saving-label')  || 'Saving...';
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
                        $.each(xhr.responseJSON.errors, function (field, msgs) {
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


// Globals — callable from inline onclick attributes in Blade templates
window.ProjectList    = ProjectList;
window.AssessmentList = AssessmentList;
window.AssessmentForm = AssessmentForm;


$(function () {
    ProjectList.init();
    ProjectForm.init();
});
