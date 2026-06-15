@extends('admin.layout.index')
@section('admin-title', 'Project Reports')

@section('content')
<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">

        <div class="page-title-row justify-content-between mb-3">
            <h4 class="mb-0 ms-3"><i class="fa fa-chart-bar me-2"></i>Project Reports</h4>
        </div>

        {{-- Project Selector --}}
        <div class="whiteBg mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Select Project</label>
                    <select id="reportProjectSelect" class="form-select">
                        <option value="">— Choose a project —</option>
                        @foreach($projects as $p)
                            <option value="{{ encrypt_id($p->id) }}">
                                {{ $p->project_name }}
                                @if($p->client_name) ({{ $p->client_name }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Report Container --}}
        <div id="reportContainer">
            <div class="text-center py-5" id="reportPlaceholder">
                <i class="fa fa-chart-bar fa-3x text-muted d-block mb-3 opacity-25"></i>
                <p class="text-muted">Select a project above to view its deployment report.</p>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    var loadUrl  = '{{ route("admin.project-reports.load") }}';
    var preselect = '{{ $selectedProjectId ?? "" }}';
    var $select   = $('#reportProjectSelect');
    var $container = $('#reportContainer');

    function loadReport(projectId) {
        $container.html(
            '<div class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>'
        );
        $.get(loadUrl, { project_id: projectId })
            .done(function (html) {
                $container.html(html);
            })
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
        } else {
            $container.html(
                '<div class="text-center py-5"><i class="fa fa-chart-bar fa-3x text-muted d-block mb-3 opacity-25"></i><p class="text-muted">Select a project above to view its deployment report.</p></div>'
            );
        }
    });

    // Auto-load if navigated here with a project_id (e.g. from project list Report button)
    if (preselect) {
        $select.val(preselect);
        loadReport(preselect);
    }
})();
</script>
@endpush
