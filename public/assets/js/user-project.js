var UserProjectList = (function () {

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
            if (!$('#userProjectAccordion').length) return;
            this.bindSearch();
            this.bindPagination();
            this.loadProjects(1);
            this.bindAccordion();
        },

        loadProjects: function (page) {
            currentPage    = parseInt(page, 10) || 1;
            var $accordion = $('#userProjectAccordion');
            var $empty     = $('#userProjectEmpty');
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

            $empty.addClass('d-none');

            $.get(window.userProjectListUrl, {
                keyword: $('#search-keyword').val(),
                page:    currentPage,
            }, function (res) {
                $wrapper.find('.dataTables_processing').remove();
                var total = res.total || 0;

                if (!res.html || res.count === 0) {
                    $accordion.empty();
                    $empty.removeClass('d-none');
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
                $accordion.empty();
                if (typeof toastr !== 'undefined') toastr.error('Failed to load projects.');
            });
        },

        bindSearch: function () {
            var self = this;
            $('#search-btn').on('click', function () { self.loadProjects(1); });
            $('#reset-btn').on('click', function () {
                $('#search-keyword').val('');
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
                var $top = $('#userProjectAccordion').closest('.card');
                if ($top.length) {
                    $('html,body').animate({ scrollTop: $top.offset().top - 20 }, 200);
                }
            });
        },

        bindAccordion: function () {
            $(document).on('click', '.toggle-project-btn', function () {
                var projectId = $(this).data('project-id');
                var $icon     = $(this).find('.toggle-icon');
                var $panel    = $('#assessment-panel-' + projectId);
                var $list     = $('#assessment-list-' + projectId);

                $panel.toggleClass('d-none');
                $icon.toggleClass('open');

                if (!$panel.hasClass('d-none') && $list.data('loaded') === false) {
                    UserProjectList.loadAssessments(projectId);
                }
            });
        },

        loadAssessments: function (projectId) {
            var $list = $('#assessment-list-' + projectId);
            $list.data('loaded', true);

            $.get(window.userForProjectBaseUrl + '/' + projectId, function (res) {
                $list.html(res.html);
            }).fail(function () {
                $list.html('<div class="text-center py-3 text-danger small"><i class="fa fa-exclamation-circle me-1"></i>Failed to load assessments.</div>');
            });
        }
    };
})();

$(function () {
    UserProjectList.init();
});
