$(function () {
    var $projectSelect    = $('#reportProjectSelect');
    if (!$projectSelect.length) return;

    var $assessmentSelect = $('#reportAssessmentSelect');
    var $generateBtn      = $('#generateReportBtn');
    var $resetBtn         = $('#resetReportBtn');
    var $container        = $('#reportContainer');

    var searchUrl      = $projectSelect.data('search-url');
    var assessmentsUrl = $projectSelect.data('assessments-url');
    var loadUrl        = $assessmentSelect.data('load-url');

    // ── Project Select2 (AJAX search) ────────────────────────────────
    $projectSelect.select2({
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
            processResults: function (data) { return data; }
        }
    });

    $projectSelect.on('select2:open', function () {
        setTimeout(function () {
            var field = document.querySelector('.select2-search--dropdown .select2-search__field');
            if (field) { field.value = ''; field.dispatchEvent(new Event('input', { bubbles: true })); }
        }, 10);
    });

    // ── Assessment Select2 helpers ────────────────────────────────────
    function isAssessmentSelect2Active() {
        return $assessmentSelect.hasClass('select2-hidden-accessible');
    }

    function destroyAssessmentSelect2() {
        if (isAssessmentSelect2Active()) {
            $assessmentSelect.select2('destroy');
        }
    }

    function initAssessmentSelect2() {
        $assessmentSelect.select2({
            width: '100%',
            placeholder: '— Choose an assessment —',
        });
    }

    // ── Fetch assessments for a project ──────────────────────────────
    function fetchAssessments(projectId, preselectId, autoGenerate) {
        destroyAssessmentSelect2();

        $assessmentSelect
            .html('<option value="">Loading assessments...</option>')
            .prop('disabled', true);
        $generateBtn.prop('disabled', true);

        if (!autoGenerate) {
            hideReport();
        }

        $.get(assessmentsUrl, { project_id: projectId })
            .done(function (res) {
                $assessmentSelect.html('<option value="">— Choose an assessment —</option>');

                if (!res.results || res.results.length === 0) {
                    $assessmentSelect
                        .append('<option disabled>No assessments found for this project</option>')
                        .prop('disabled', true);
                    return;
                }

                $.each(res.results, function (i, item) {
                    var sel = (preselectId && item.id === preselectId) ? ' selected' : '';
                    $assessmentSelect.append(
                        '<option value="' + item.id + '"' + sel + '>' + item.text + '</option>'
                    );
                });

                $assessmentSelect.prop('disabled', false);
                initAssessmentSelect2();
                updateGenerateBtn();

                if (autoGenerate && preselectId && $assessmentSelect.val()) {
                    generateReport();
                }
            })
            .fail(function () {
                $assessmentSelect
                    .html('<option value="">— Failed to load —</option>')
                    .prop('disabled', true);
            });
    }

    // ── Enable/disable Generate button ───────────────────────────────
    function updateGenerateBtn() {
        $generateBtn.prop('disabled', !($projectSelect.val() && $assessmentSelect.val()));
    }

    // ── Hide/clear the report container ──────────────────────────────
    function hideReport() {
        $container.addClass('d-none').empty();
    }

    // ── Load the assessment report ────────────────────────────────────
    function generateReport() {
        var assessmentId = $assessmentSelect.val();
        if (!assessmentId) return;

        $generateBtn.prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin me-1"></i>Loading...');

        $container
            .removeClass('d-none')
            .html(
                '<div class="text-center py-5 text-muted">' +
                '<i class="fa fa-spinner fa-spin fa-2x d-block mb-3"></i>' +
                '<span>Generating report...</span>' +
                '</div>'
            );

        $.get(loadUrl, { assessment_id: assessmentId })
            .done(function (html) { $container.html(html); })
            .fail(function () {
                $container.html(
                    '<div class="alert alert-danger mx-0 mt-0">' +
                    '<i class="fa fa-triangle-exclamation me-2"></i>' +
                    'Failed to load report. Please try again.' +
                    '</div>'
                );
            })
            .always(function () {
                $generateBtn.prop('disabled', false)
                    .html('<i class="fa fa-magnifying-glass me-1"></i>Generate Report');
            });
    }

    // ── Reset everything ──────────────────────────────────────────────
    function resetAll() {
        $projectSelect.val(null).trigger('change.select2');
        destroyAssessmentSelect2();
        $assessmentSelect
            .html('<option value="">— Select a project first —</option>')
            .prop('disabled', true);
        $generateBtn.prop('disabled', true);
        hideReport();
    }

    // ── Event bindings ────────────────────────────────────────────────
    $projectSelect.on('change', function () {
        var projectId = $(this).val();
        if (projectId) {
            fetchAssessments(projectId, '', false);
        } else {
            destroyAssessmentSelect2();
            $assessmentSelect
                .html('<option value="">— Select a project first —</option>')
                .prop('disabled', true);
            $generateBtn.prop('disabled', true);
            hideReport();
        }
    });

    // Native change fires for both plain select and Select2
    $assessmentSelect.on('change', function () {
        updateGenerateBtn();
        // Clear stale report when user picks a different assessment
        hideReport();
    });

    $generateBtn.on('click', function (e) {
        e.preventDefault();
        generateReport();
    });

    $resetBtn.on('click', function (e) {
        e.preventDefault();
        resetAll();
    });

    // ── Preselect on page load (URL contains project_id + assessment_id) ──
    var preProject    = $projectSelect.data('preselect');
    var preAssessment = $assessmentSelect.data('preselect');

    if (preProject) {
        fetchAssessments(preProject, preAssessment, !!preAssessment);
    }
});
