@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Dashboard')

<div class="container-fluid">
    <div class="page-content-wrapper">

        <div class="page-title-row d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="page-title-row-text">
                <h4>Dashboard</h4>
                <p class="page-title-row-sub">An overview of your platform activity at a glance.</p>
            </div>
            <div style="min-width:160px">
                <select id="dashboardStatusFilter" class="form-select" data-no-select2
                    data-url="{{ route('admin.dashboard.data') }}">
                    <option value="1" selected>Active Projects</option>
                    <option value="0">Inactive Projects</option>
                </select>
            </div>
        </div>

        {{-- Dashboard content wrapper --}}
        <div id="dashboardContent" style="position:relative">

            {{-- Loader overlay --}}
            <div id="dashboardLoader" style="display:none; position:absolute; inset:0; background:rgba(255,255,255,0.7); z-index:10; align-items:center; justify-content:center;">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin fa-2x" style="color:var(--primarycolor)"></i>
                </div>
            </div>

            {{-- Compact Stat Cards --}}
            <div class="row g-3 dash-compact" id="statCards">
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dash-item">
                        <a href="{{ route('admin.users.userIndex') }}">
                            <div class="dash-item-icon"><i class="fa fa-users"></i></div>
                            <div>
                                <h6>Total Verifiers</h6>
                                <p class="mb-0" id="stat-userCount">{{ $userCount ?? 0 }}</p>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dash-item">
                        <a href="{{ route('admin.project.index') }}">
                            <div class="dash-item-icon"><i class="fa fa-diagram-project"></i></div>
                            <div>
                                <h6>Total Projects</h6>
                                <p class="mb-0" id="stat-totalProjects">{{ $totalProjects }}</p>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dash-item">
                        <a href="{{ route('admin.project.index') }}">
                            <div class="dash-item-icon"><i class="fa fa-circle-check"></i></div>
                            <div>
                                <h6>Verified Projects</h6>
                                <p class="mb-0" id="stat-verifiedProjects">{{ $verifiedProjects }}</p>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dash-item">
                        <a href="{{ route('admin.project.index') }}">
                            <div class="dash-item-icon"><i class="fa fa-clock"></i></div>
                            <div>
                                <h6>Pending Verification</h6>
                                <p class="mb-0" id="stat-pendingProjects">{{ $pendingProjects }}</p>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dash-item">
                        <a href="{{ route('admin.project-reports.index') }}">
                            <div class="dash-item-icon"><i class="fa fa-percent"></i></div>
                            <div>
                                <h6>Overall Completion</h6>
                                <p class="mb-0" id="stat-overallPercent">{{ $overallPercent }}%</p>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dash-item">
                        <a href="{{ route('admin.project-reports.index') }}">
                            <div class="dash-item-icon"><i class="fa fa-list-check"></i></div>
                            <div>
                                <h6>Completed Items</h6>
                                <p class="mb-0" id="stat-completedItems">{{ $completedItems }}</p>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dash-item">
                        <a href="{{ route('admin.project-reports.index') }}">
                            <div class="dash-item-icon"><i class="fa fa-hourglass-half"></i></div>
                            <div>
                                <h6>Pending Items</h6>
                                <p class="mb-0" id="stat-pendingItems">{{ $pendingItems }}</p>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dash-item">
                        <a href="{{ route('admin.reports.assessment-list') }}">
                            <div class="dash-item-icon"><i class="fa fa-clipboard-list"></i></div>
                            <div>
                                <h6>Total Assessments</h6>
                                <p class="mb-0" id="stat-totalAssessments">{{ $totalAssessments }}</p>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dash-item">
                        <a href="{{ route('admin.reports.assessment-list') }}?status=completed">
                            <div class="dash-item-icon"><i class="fa fa-clipboard-check"></i></div>
                            <div>
                                <h6>Completed Assessments</h6>
                                <p class="mb-0" id="stat-completedAssessments">{{ $completedAssessments }}</p>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dash-item">
                        <a href="{{ route('admin.reports.assessment-list') }}?status=pending">
                            <div class="dash-item-icon"><i class="fa fa-clock-rotate-left"></i></div>
                            <div>
                                <h6>Pending Assessments</h6>
                                <p class="mb-0" id="stat-pendingAssessments">{{ $pendingAssessments }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Assessment-wise Progress --}}
            <div class="row g-3 mt-1">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header section-header">
                            <h5 class="mb-0"><i class="fa fa-chart-pie me-2"></i>Assessment-wise Progress</h5>
                        </div>
                        <div class="card-body" id="assessmentProgressBody">
                            @if ($assessmentProgress->isEmpty())
                                <p class="text-muted mb-0">No assessments found for the selected project status.</p>
                            @else
                                <div class="row g-3">
                                    @foreach ($assessmentProgress as $row)
                                        <div class="col-lg-6">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div class="text-truncate me-3">
                                                    <span class="fw-medium d-block" style="font-size:.85rem" title="{{ $row['assessment']->name }}">
                                                        {{ $row['assessment']->name }}
                                                    </span>
                                                    <small class="text-muted">{{ $row['project']?->project_name ?? '' }}</small>
                                                </div>
                                                <small class="text-muted text-nowrap">
                                                    {{ $row['completed'] }}/{{ $row['total'] }} ({{ $row['percent'] }}%)
                                                </small>
                                            </div>
                                            <div class="progress" style="height:6px">
                                                <div class="progress-bar {{ $row['percent'] === 100 ? 'bg-success' : ($row['percent'] >= 50 ? 'bg-primary' : 'bg-warning') }}"
                                                    role="progressbar"
                                                    style="width:{{ $row['percent'] }}%"
                                                    aria-valuenow="{{ $row['percent'] }}"
                                                    aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Project-wise Progress + Recent Assessment Activity --}}
            <div class="row g-3 mt-1">
                <div class="col-lg-6">
                    <div class="card custom-card h-100">
                        <div class="card-header section-header">
                            <h5 class="mb-0"><i class="fa fa-chart-bar me-2"></i>Project-wise Progress</h5>
                        </div>
                        <div class="card-body" id="projectProgressBody">
                            @if ($projectProgress->isEmpty())
                                <p class="text-muted mb-0">No projects found.</p>
                            @else
                                @foreach ($projectProgress as $row)
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-medium text-truncate me-2" style="max-width:240px;font-size:.85rem"
                                                title="{{ $row['project']->project_name }}">
                                                {{ $row['project']->project_name }}
                                            </span>
                                            <small class="text-muted text-nowrap">
                                                {{ $row['completed'] }}/{{ $row['total'] }} ({{ $row['percent'] }}%)
                                            </small>
                                        </div>
                                        <div class="progress" style="height:6px">
                                            <div class="progress-bar {{ $row['percent'] === 100 ? 'bg-success' : ($row['percent'] >= 50 ? 'bg-primary' : 'bg-warning') }}"
                                                role="progressbar" style="width:{{ $row['percent'] }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card custom-card h-100">
                        <div class="card-header section-header">
                            <h5 class="mb-0"><i class="fa fa-clipboard-list me-2"></i>Recent Assessment Activity</h5>
                        </div>
                        <div class="card-body p-0" id="recentAssessmentActivityBody">
                            @if ($recentAssessmentActivity->isEmpty())
                                <p class="text-muted p-3 mb-0">No assessment activity yet.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0 align-middle">
                                        <thead>
                                            <tr>
                                                <th>Assessment</th>
                                                <th>Item</th>
                                                <th>By</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($recentAssessmentActivity as $act)
                                                <tr>
                                                    <td>
                                                        <div class="fw-medium" style="font-size:.82rem">{{ $act->assessment?->name ?? '—' }}</div>
                                                        <small class="text-muted">{{ $act->assessment?->project?->project_name ?? '' }}</small>
                                                    </td>
                                                    <td class="text-truncate" style="max-width:130px;font-size:.82rem"
                                                        title="{{ $act->checklistItem?->checklist_item }}">
                                                        {{ $act->checklistItem?->checklist_item ?? '—' }}
                                                    </td>
                                                    <td style="font-size:.82rem">
                                                        {{ $act->checkedBy ? $act->checkedBy->first_name . ' ' . $act->checkedBy->last_name : '—' }}
                                                    </td>
                                                    <td class="text-nowrap" style="font-size:.82rem">
                                                        {{ $act->checked_at?->format('d M Y') ?? '—' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- #dashboardContent --}}

    </div>
</div>

@endsection

@push('styles')
<style>
/* Compact dashboard stat cards */
.dash-compact .dash-item {
    padding: 12px 14px;
    margin-bottom: 0;
    border-radius: 12px;
}
.dash-compact .dash-item .dash-item-icon {
    width: 46px;
    height: 46px;
    border-radius: 8px;
    font-size: 17px;
    flex-shrink: 0;
}
.dash-compact .dash-item a p {
    font-size: 22px;
    font-weight: 500;
    line-height: 1.2;
}
.dash-compact .dash-item h6 {
    font-size: 10.5px;
    margin-bottom: 2px;
}

/* Two-column grid inside assessment progress body */
#assessmentProgressBody .row > [class*="col"] {
    padding-bottom: 4px;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/dashboard.js') }}"></script>
@endpush
