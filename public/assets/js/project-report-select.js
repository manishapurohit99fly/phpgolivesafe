$(function () {
    var $select    = $('#reportProjectSelect');
    if (!$select.length) return;

    var searchUrl  = $select.data('search-url');
    var loadUrl    = $select.data('load-url');
    var preselect  = $select.data('preselect');
    var $container = $('#reportContainer');

    var emptyHtml  = $select.data('empty-msg')
        ? '<div class="text-center py-5"><i class="fa fa-chart-bar fa-3x text-muted d-block mb-3 opacity-25"></i><p class="text-muted">' + $select.data('empty-msg') + '</p></div>'
        : '';

    $select.select2({
        width: '100%',
        placeholder: '— Choose a project —',
        minimumInputLength: 0,
        ajax: {
            url: searchUrl,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term !== undefined ? params.term : '', page: params.page || 1 };
            },
            processResults: function (data) {
                return data;
            }
        }
    });

    $select.on('select2:open', function () {
        setTimeout(function () {
            var field = document.querySelector('.select2-search--dropdown .select2-search__field');
            if (field) { field.value = ''; field.dispatchEvent(new Event('input', { bubbles: true })); }
        }, 10);
    });

    function loadReport(projectId) {
        $container.html(
            '<div class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>'
        );
        $.get(loadUrl, { project_id: projectId })
            .done(function (html) { $container.html(html); })
            .fail(function () {
                $container.html(
                    '<div class="text-center py-4 text-danger"><i class="fa fa-triangle-exclamation me-1"></i>Failed to load report. Please try again.</div>'
                );
            });
    }

    $select.on('change', function () {
        var val = $(this).val();
        if (val) {
            loadReport(val);
        } else if (emptyHtml) {
            $container.html(emptyHtml);
        }
    });

    if (preselect) {
        loadReport(preselect);
    }
});
