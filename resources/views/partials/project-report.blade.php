{{--
    Shared report partial — rendered by admin report, user report, and public report.
    Expects: $reportData (array from ProjectReportService), $isPublic (bool, optional)
--}}
@php
    $project        = $reportData['project'];
    $total          = $reportData['total'];
    $completed      = $reportData['completed'];
    $pending        = $reportData['pending'];
    $percent        = $reportData['percent'];
    $categories     = $reportData['categories'];
    $verifiedUsers  = $reportData['verifiedUsers'];
    $lastVerifiedAt = $reportData['lastVerifiedAt'];
    $deployNotes    = $reportData['deploymentNotes'];
    $assignedUsers  = $reportData['assignedUsers'];
    $shareToken     = $reportData['shareToken'];
    $isPublic       = $isPublic ?? false;
    $chartId        = 'reportChart_' . $project->id;
@endphp

{{-- ── Project Info Card ──────────────────────────────────────────────── --}}
<div class="card report-card mb-4">
    <div class="card-header report-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="mb-0 fw-semibold">
                <i class="fa fa-diagram-project me-2 text-primary"></i>{{ $project->project_name }}
            </h5>
            @if($project->client_name)
                <small class="text-muted">Client: {{ $project->client_name }}</small>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($project->status)
                <span class="badge bg-success-subtle text-success px-3 py-2">Active</span>
            @else
                <span class="badge bg-secondary-subtle text-secondary px-3 py-2">Inactive</span>
            @endif
            @if(!$isPublic && $shareToken)
                <button type="button" class="btn btn-sm btn-outline-primary"
                    onclick="navigator.clipboard.writeText('{{ route('project.public.report', $shareToken) }}').then(function(){toastr.success('Share URL copied!')})">
                    <i class="fa fa-link me-1"></i>Copy Share URL
                </button>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @if($project->project_url)
            <div class="col-md-4">
                <p class="report-meta-label">Project URL</p>
                <a href="{{ $project->project_url }}" target="_blank" rel="noopener noreferrer"
                    class="text-primary text-decoration-none small text-break">
                    <i class="fa fa-arrow-up-right-from-square me-1"></i>{{ \Illuminate\Support\Str::limit($project->project_url, 50) }}
                </a>
            </div>
            @endif
            <div class="col-md-4">
                <p class="report-meta-label">Created Date</p>
                <p class="mb-0 small">{{ getDateInFormat($project->created_at) }}</p>
            </div>
            @if($assignedUsers && $assignedUsers->count())
            <div class="col-md-4">
                <p class="report-meta-label">Assigned Verifiers</p>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($assignedUsers as $u)
                        <span class="badge bg-primary-subtle text-primary">{{ trim($u->first_name . ' ' . $u->last_name) }}</span>
                    @endforeach
                </div>
            </div>
            @endif
            @if($lastVerifiedAt || $verifiedUsers->count())
            <div class="col-md-4">
                <p class="report-meta-label">Last Verified</p>
                <p class="mb-0 small">
                    @if($lastVerifiedAt)
                        {{ \Carbon\Carbon::parse($lastVerifiedAt)->format('d M Y, h:i A') }}
                    @else
                        <span class="text-muted">Not yet verified</span>
                    @endif
                </p>
            </div>
            @if($verifiedUsers->count())
            <div class="col-md-8">
                <p class="report-meta-label">Verified By</p>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($verifiedUsers as $u)
                        <span class="badge bg-success-subtle text-success">
                            <i class="fa fa-user-check me-1"></i>{{ trim($u->first_name . ' ' . $u->last_name) }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif
            @endif
        </div>
    </div>
</div>

{{-- ── Summary Cards ───────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="report-stat-card">
            <div class="report-stat-icon bg-primary-subtle text-primary"><i class="fa fa-list-check"></i></div>
            <div class="report-stat-number">{{ $total }}</div>
            <div class="report-stat-label">Total Items</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="report-stat-card">
            <div class="report-stat-icon bg-success-subtle text-success"><i class="fa fa-circle-check"></i></div>
            <div class="report-stat-number text-success">{{ $completed }}</div>
            <div class="report-stat-label">Completed</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="report-stat-card">
            <div class="report-stat-icon bg-warning-subtle text-warning"><i class="fa fa-clock"></i></div>
            <div class="report-stat-number text-warning">{{ $pending }}</div>
            <div class="report-stat-label">Pending</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="report-stat-card">
            <div class="report-stat-icon {{ $percent >= 100 ? 'bg-success-subtle text-success' : ($percent >= 75 ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning') }}">
                <i class="fa fa-gauge-high"></i>
            </div>
            <div class="report-stat-number {{ $percent >= 100 ? 'text-success' : ($percent >= 75 ? 'text-primary' : 'text-warning') }}">
                {{ $percent }}%
            </div>
            <div class="report-stat-label">Completion</div>
        </div>
    </div>
</div>

{{-- ── Chart + Deployment Notes ────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Doughnut Chart --}}
    <div class="col-md-5">
        <div class="card report-card h-100">
            <div class="card-header report-card-header">
                <h6 class="mb-0 fw-semibold"><i class="fa fa-chart-pie me-2 text-primary"></i>Completion Overview</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center py-4">
                @if($total > 0)
                    {{-- Responsive wrapper: fills the card, capped at 220px, stays square --}}
                    <div class="report-chart-wrapper">
                        <canvas id="{{ $chartId }}"></canvas>
                        <div class="report-chart-center">
                            <span class="report-chart-pct">{{ $percent }}%</span>
                            <span class="report-chart-lbl">Complete</span>
                        </div>
                    </div>
                @else
                    <p class="text-muted small text-center py-4">
                        <i class="fa fa-chart-pie fa-2x d-block mb-2 opacity-25"></i>
                        No checklist items assigned.
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Deployment Notes --}}
    <div class="col-md-7">
        <div class="card report-card h-100">
            <div class="card-header report-card-header">
                <h6 class="mb-0 fw-semibold"><i class="fa fa-file-lines me-2 text-primary"></i>Deployment Verification Notes</h6>
            </div>
            <div class="card-body">
                @if($deployNotes)
                    <p class="mb-0 small" style="white-space: pre-wrap;">{{ $deployNotes }}</p>
                @else
                    <p class="text-muted small mb-0"><i class="fa fa-info-circle me-1"></i>No deployment notes added yet.</p>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Category Progress Bars ──────────────────────────────────────────── --}}
@if($categories->count())
<div class="card report-card mb-4">
    <div class="card-header report-card-header">
        <h6 class="mb-0 fw-semibold"><i class="fa fa-layer-group me-2 text-primary"></i>Category-wise Progress</h6>
    </div>
    <div class="card-body pb-2">
        @foreach($categories as $group)
        @php
            $cat        = $group['category'];
            $catTotal   = $group['total'];
            $catDone    = $group['completed'];
            $catPct     = $group['percent'];
            $catColour  = $catPct >= 75 ? 'bg-success' : ($catPct >= 40 ? 'bg-warning' : 'bg-danger');
        @endphp
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-medium small">{{ $cat->category_name }}</span>
                <span class="small text-muted">{{ $catDone }} / {{ $catTotal }}</span>
            </div>
            <div class="progress mb-2" style="height: 8px; border-radius: 6px;">
                <div class="progress-bar {{ $catColour }}"
                    role="progressbar"
                    style="width: {{ $catPct }}%; border-radius: 6px;"
                    aria-valuenow="{{ $catPct }}" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>

            {{-- Item detail --}}
            <div class="ps-2">
                @foreach($group['items'] as $pc)
                @php $checkedByName = $pc->checkedBy ? trim($pc->checkedBy->first_name . ' ' . $pc->checkedBy->last_name) : null; @endphp
                <div class="report-item-row d-flex align-items-start gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <span class="mt-1 flex-shrink-0">
                        @if($pc->is_checked)
                            <i class="fa fa-circle-check text-success"></i>
                        @else
                            <i class="fa fa-circle text-muted opacity-50"></i>
                        @endif
                    </span>
                    <div class="flex-grow-1 min-w-0">
                        <span class="small {{ $pc->is_checked ? 'text-muted text-decoration-line-through' : '' }}">
                            {{ $pc->checklistItem->checklist_item }}
                        </span>
                        @if($pc->is_checked && $checkedByName)
                            <div class="report-item-meta">
                                <i class="fa fa-user-check me-1"></i>{{ $checkedByName }}
                                @if($pc->checked_at)
                                    &nbsp;&bull;&nbsp;<i class="fa fa-calendar-check me-1"></i>{{ \Carbon\Carbon::parse($pc->checked_at)->format('d M Y, h:i A') }}
                                @endif
                            </div>
                        @endif
                    </div>
                    <span class="flex-shrink-0">
                        @if($pc->is_checked)
                            <span class="badge bg-success-subtle text-success">Done</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning">Pending</span>
                        @endif
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Inline styles ────────────────────────────────────────────────────── --}}
@once
<style>
.report-card { border: 1px solid #e9ecef; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.05); }
.report-card-header { background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 14px 20px; border-radius: 12px 12px 0 0 !important; }

.report-stat-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 18px 16px 14px;
    text-align: center;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.report-stat-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    margin-bottom: 10px;
}
.report-stat-number { font-size: 1.8rem; font-weight: 700; line-height: 1; margin-bottom: 4px; }
.report-stat-label  { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; }
.report-meta-label  { font-size: 0.72rem; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }

/* ── Responsive chart wrapper ── */
.report-chart-wrapper {
    position: relative;
    width: 100%;
    max-width: 220px;   /* cap on large screens */
    margin: 0 auto;     /* centre inside flex card-body */
    aspect-ratio: 1 / 1; /* always perfectly square */
}
.report-chart-wrapper canvas {
    width: 100% !important;
    height: 100% !important;
    display: block;
}
.report-chart-center {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none;
}
.report-chart-pct { display: block; font-size: 1.6rem; font-weight: 700; line-height: 1.1; }
.report-chart-lbl { display: block; font-size: 0.7rem; color: #6c757d; text-transform: uppercase; }

.report-item-meta  { font-size: 0.72rem; color: #6b7280; margin-top: 2px; }
</style>
@endonce

{{--
    Chart init — inline script, polls until Chart.js is available.
    Works for full-page render (Chart.js loaded at end of body) AND
    for AJAX injection via jQuery .html() (Chart.js already in page).
--}}
@if($total > 0)
<script>
(function () {
    var chartId   = '{{ $chartId }}';
    var completed = {{ $completed }};
    var pending   = {{ $pending }};

    function tryInitChart() {
        if (typeof Chart === 'undefined') { setTimeout(tryInitChart, 50); return; }

        var el = document.getElementById(chartId);
        if (!el) return;

        // Destroy any previous instance on the same canvas (e.g. AJAX re-renders)
        var existing = Chart.getChart(el);
        if (existing) { existing.destroy(); }

        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Pending'],
                datasets: [{
                    data: [completed, pending],
                    backgroundColor: ['#22c55e', '#f59e0b'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,           // ← let Chart.js track the canvas size
                maintainAspectRatio: false, // ← the wrapper's aspect-ratio CSS handles shape
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (c) {
                                var t = c.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                var p = t > 0 ? Math.round((c.raw / t) * 100) : 0;
                                return ' ' + c.label + ': ' + c.raw + ' (' + p + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    tryInitChart();
})();
</script>
@endif