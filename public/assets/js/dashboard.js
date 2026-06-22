(function () {

    var $filter = $('#dashboardStatusFilter');
    var dataUrl = $filter.data('url');

    function showLoader() {
        $('#dashboardLoader').css('display', 'flex');
    }

    function hideLoader() {
        $('#dashboardLoader').hide();
    }

    function buildProjectProgressHtml(rows) {
        if (!rows.length) {
            return '<p class="text-muted mb-0">No projects found.</p>';
        }
        var html = '';
        rows.forEach(function (r) {
            var barClass = r.percent === 100 ? 'bg-success' : (r.percent >= 50 ? 'bg-primary' : 'bg-warning');
            html += '<div class="mb-3">'
                + '<div class="d-flex justify-content-between align-items-center mb-1">'
                + '<span class="fw-medium text-truncate me-2" style="max-width:240px;font-size:.85rem" title="' + r.name + '">' + r.name + '</span>'
                + '<small class="text-muted text-nowrap">' + r.completed + '/' + r.total + ' (' + r.percent + '%)</small>'
                + '</div>'
                + '<div class="progress" style="height:6px">'
                + '<div class="progress-bar ' + barClass + '" role="progressbar" style="width:' + r.percent + '%"></div>'
                + '</div></div>';
        });
        return html;
    }

    function buildRecentAssessmentActivityHtml(rows) {
        if (!rows.length) {
            return '<p class="text-muted p-3 mb-0">No assessment activity yet.</p>';
        }
        var html = '<div class="table-responsive"><table class="table table-sm mb-0 align-middle">'
            + '<thead><tr><th>Assessment</th><th>Item</th><th>By</th><th>Date</th></tr></thead><tbody>';
        rows.forEach(function (r) {
            html += '<tr>'
                + '<td><div class="fw-medium" style="font-size:.82rem">' + r.assessment + '</div>'
                + '<small class="text-muted">' + r.project + '</small></td>'
                + '<td class="text-truncate" style="max-width:130px;font-size:.82rem" title="' + r.item + '">' + r.item + '</td>'
                + '<td style="font-size:.82rem">' + r.verified_by + '</td>'
                + '<td class="text-nowrap" style="font-size:.82rem">' + r.date + '</td>'
                + '</tr>';
        });
        html += '</tbody></table></div>';
        return html;
    }

    function buildAssessmentProgressHtml(rows) {
        if (!rows.length) {
            return '<p class="text-muted mb-0">No assessments found for the selected project status.</p>';
        }
        var html = '<div class="row g-3">';
        rows.forEach(function (r) {
            var barClass = r.percent === 100
                ? 'bg-success'
                : (r.percent >= 50 ? 'bg-primary' : 'bg-warning');
            html += '<div class="col-lg-6">'
                + '<div class="d-flex justify-content-between align-items-start mb-1">'
                + '<div class="text-truncate me-3">'
                + '<span class="fw-medium d-block" style="font-size:.85rem" title="' + r.name + '">' + r.name + '</span>'
                + '<small class="text-muted">' + r.project + '</small>'
                + '</div>'
                + '<small class="text-muted text-nowrap">' + r.completed + '/' + r.total + ' (' + r.percent + '%)</small>'
                + '</div>'
                + '<div class="progress" style="height:6px">'
                + '<div class="progress-bar ' + barClass + '" role="progressbar"'
                + ' style="width:' + r.percent + '%"'
                + ' aria-valuenow="' + r.percent + '" aria-valuemin="0" aria-valuemax="100"></div>'
                + '</div>'
                + '</div>';
        });
        html += '</div>';
        return html;
    }

    function loadData(status) {
        showLoader();
        $.ajax({
            url: dataUrl,
            data: { status: status },
            success: function (res) {
                if (!res.success) return;
                $('#stat-userCount').text(res.userCount);
                $('#stat-totalProjects').text(res.totalProjects);
                $('#stat-verifiedProjects').text(res.verifiedProjects);
                $('#stat-pendingProjects').text(res.pendingProjects);
                $('#stat-overallPercent').text(res.overallPercent + '%');
                $('#stat-completedItems').text(res.completedItems);
                $('#stat-pendingItems').text(res.pendingItems);
                $('#stat-totalAssessments').text(res.totalAssessments);
                $('#stat-completedAssessments').text(res.completedAssessments);
                $('#stat-pendingAssessments').text(res.pendingAssessments);
                $('#projectProgressBody').html(buildProjectProgressHtml(res.projectProgress));
                $('#recentAssessmentActivityBody').html(buildRecentAssessmentActivityHtml(res.recentAssessmentActivity));
                $('#assessmentProgressBody').html(buildAssessmentProgressHtml(res.assessmentProgress));
            },
            error: function () {
                toastr.error('Failed to load dashboard data.');
            },
            complete: function () {
                hideLoader();
            }
        });
    }

    $filter.on('change', function () {
        loadData($(this).val());
    });

}());
