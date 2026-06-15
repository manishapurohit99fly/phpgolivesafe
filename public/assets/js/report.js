/*
 * Project Report Dashboard
 *
 * Loads report data via AJAX for the selected project,
 * renders summary cards, a Chart.js doughnut chart, and
 * category-wise progress bars. Refreshes automatically
 * whenever the project dropdown changes — no page reload.
 */

const ReportDashboard = (function () {

    var chart       = null;
    var $dashboard  = $('#reportDashboard');
    var dataUrlTpl  = $dashboard.data('data-url');   // e.g. /admin/projects/report-data/:id
    var defaultId   = $dashboard.data('default-id');

    // ----------------------------------------------------------------
    // Boot
    // ----------------------------------------------------------------

    function init() {
        if (!$dashboard.length) return;

        bindDropdown();
        loadReport(defaultId);
    }

    // ----------------------------------------------------------------
    // Project dropdown
    // ----------------------------------------------------------------

    function bindDropdown() {
        $('#projectSelect').on('change', function () {
            loadReport($(this).val());
        });
    }

    // ----------------------------------------------------------------
    // Fetch report data and render
    // ----------------------------------------------------------------

    function loadReport(encId) {
        if (!encId) return;

        var url = dataUrlTpl.replace(':id', encId);

        $('#reportSpinner').removeClass('d-none');

        $.ajax({
            url:      url,
            type:     'GET',
            dataType: 'json',

            success: function (res) {
                renderReport(res);
            },

            error: function () {
                toastr.error('Could not load report data. Please try again.');
            },

            complete: function () {
                $('#reportSpinner').addClass('d-none');
            }
        });
    }

    // ----------------------------------------------------------------
    // Render everything from the server payload
    // ----------------------------------------------------------------

    function renderReport(res) {
        var d = res.data;

        // Project meta strip
        $('#metaClient').text(res.client_name || '—');

        // Summary cards
        $('#rTotal').text(d.total);
        $('#rCompleted').text(d.completed);
        $('#rPending').text(d.pending);
        $('#rPercent').text(d.percent + '%');

        if (d.total === 0) {
            $('#dashboardContent').addClass('d-none');
            $('#emptyState').removeClass('d-none');
            return;
        }

        $('#emptyState').addClass('d-none');
        $('#dashboardContent').removeClass('d-none');

        // Pie / doughnut chart
        renderChart(d.completed, d.pending, d.percent);

        // Category bars
        renderCategories(d.categoryStats);
    }

    // ----------------------------------------------------------------
    // Chart.js doughnut
    // ----------------------------------------------------------------

    function renderChart(completed, pending, percent) {
        var ctx = document.getElementById('completionChart');
        if (!ctx) return;

        var completedColor = '#22c55e';
        var pendingColor   = '#f59e0b';
        var emptyColor     = '#e9ecef';

        // Colour gradient based on percent
        if (percent < 40)      { completedColor = '#ef4444'; }
        else if (percent < 75) { completedColor = '#f59e0b'; pendingColor = '#d1d5db'; }
        else                   { completedColor = '#22c55e'; pendingColor = '#fde68a'; }

        var chartData = (completed === 0 && pending === 0)
            ? { values: [1], colors: [emptyColor], labels: ['No Items'] }
            : { values: [completed, pending], colors: [completedColor, pendingColor], labels: ['Completed', 'Pending'] };

        if (chart) {
            chart.data.datasets[0].data   = chartData.values;
            chart.data.datasets[0].backgroundColor = chartData.colors;
            chart.data.labels = chartData.labels;
            chart.update();
        } else {
            chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels:   chartData.labels,
                    datasets: [{
                        data:            chartData.values,
                        backgroundColor: chartData.colors,
                        borderWidth:     2,
                        borderColor:     '#fff',
                        hoverOffset:     6,
                    }]
                },
                options: {
                    cutout:        '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                    var val   = ctx.parsed;
                                    var pct   = total > 0 ? Math.round((val / total) * 100) : 0;
                                    return ctx.label + ': ' + val + ' (' + pct + '%)';
                                }
                            }
                        }
                    },
                    animation: { animateRotate: true, duration: 600 }
                }
            });
        }

        // Centre label
        $('#chartCenterPercent').text(percent + '%');
    }

    // ----------------------------------------------------------------
    // Category progress bars
    // ----------------------------------------------------------------

    function renderCategories(categories) {
        var $list = $('#categoryProgressList');

        if (!categories || categories.length === 0) {
            $list.html('<p class="text-muted text-center py-3 small">No categories found.</p>');
            return;
        }

        var html = '';
        categories.forEach(function (cat) {
            var barClass = cat.percent < 40 ? 'bg-danger'
                         : cat.percent < 75 ? 'bg-warning'
                         : 'bg-success';

            html += '<div class="cat-progress-row">'
                  + '  <div class="cat-progress-label">'
                  + '    <span class="fw-medium">' + escHtml(cat.name) + '</span>'
                  + '    <span class="text-muted small">'
                  + '      <span class="fw-semibold ' + (cat.percent >= 75 ? 'text-success' : (cat.percent >= 40 ? 'text-warning' : 'text-danger')) + '">' + cat.percent + '%</span>'
                  + '      &nbsp;<span class="text-muted">(' + cat.completed + '/' + cat.total + ')</span>'
                  + '    </span>'
                  + '  </div>'
                  + '  <div class="cat-progress-bar-wrap">'
                  + '    <div class="cat-progress-bar-fill ' + barClass + '" style="width: 0%"'
                  + '         data-target="' + cat.percent + '"></div>'
                  + '  </div>'
                  + '</div>';
        });

        $list.html(html);

        // Animate bars after paint
        requestAnimationFrame(function () {
            $list.find('.cat-progress-bar-fill').each(function () {
                var target = $(this).data('target');
                $(this).css('width', target + '%');
            });
        });
    }

    // ----------------------------------------------------------------
    // Utility
    // ----------------------------------------------------------------

    function escHtml(str) {
        return $('<div>').text(String(str)).html();
    }

    // ----------------------------------------------------------------
    // Public API
    // ----------------------------------------------------------------

    return { init: init };

})();


$(function () {
    ReportDashboard.init();
});
