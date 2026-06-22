@extends('admin.layout.index')
@section('admin-title', 'My Dashboard')

@section('content')
<div class="container-fluid">
    <div class="page-content-wrapper">

        <div class="page-title-row">
            <div class="page-title-row-text">
                <h4>My Dashboard</h4>
                <p class="page-title-row-sub">Status and progress of your assigned assessments at a glance.</p>
            </div>
        </div>

        {{-- Compact Stat Cards --}}
        <div class="row g-3 dash-compact">
            <div class="col-lg-3 col-sm-6">
                <div class="dash-item">
                    <a href="{{ route('user.project.index') }}">
                        <div class="dash-item-icon"><i class="fa fa-clipboard-list"></i></div>
                        <div>
                            <h6>Assigned Assessments</h6>
                            <p class="mb-0">{{ $totalAssigned }}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="dash-item">
                    <a href="{{ route('user.project.index') }}">
                        <div class="dash-item-icon"><i class="fa fa-clipboard-check"></i></div>
                        <div>
                            <h6>Completed Assessments</h6>
                            <p class="mb-0">{{ $completedAssessments }}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="dash-item">
                    <a href="{{ route('user.project.index') }}">
                        <div class="dash-item-icon"><i class="fa fa-clock-rotate-left"></i></div>
                        <div>
                            <h6>Pending Assessments</h6>
                            <p class="mb-0">{{ $pendingAssessments }}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="dash-item">
                    <a href="{{ route('user.reports.index') }}">
                        <div class="dash-item-icon"><i class="fa fa-percent"></i></div>
                        <div>
                            <h6>Overall Completion</h6>
                            <p class="mb-0">{{ $overallPercent }}%</p>
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
                        <h5 class="mb-0"><i class="fa fa-chart-pie me-2"></i>My Assessment Progress</h5>
                    </div>
                    <div class="card-body">
                        @if ($assessmentProgress->isEmpty())
                            <p class="text-muted mb-0">No assessments assigned to you yet.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Assessment</th>
                                            <th>Project</th>
                                            <th style="min-width:160px">Progress</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($assessmentProgress as $i => $row)
                                            <tr>
                                                <td class="text-muted">{{ $i + 1 }}</td>
                                                <td>
                                                    <span class="fw-medium">{{ $row['assessment']->name }}</span>
                                                </td>
                                                <td class="text-muted small">
                                                    {{ $row['project']?->project_name ?? '—' }}
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1" style="height:6px;border-radius:4px;">
                                                            <div class="progress-bar {{ $row['percent'] < 40 ? 'bg-danger' : ($row['percent'] < 75 ? 'bg-warning' : 'bg-success') }}"
                                                                style="width:{{ $row['percent'] }}%"></div>
                                                        </div>
                                                        <small class="text-muted text-nowrap">{{ $row['completed'] }}/{{ $row['total'] }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if ($row['submitted'])
                                                        <span class="badge bg-success">Submitted</span>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning fw-medium">Pending</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ enroute('user.assessment.checklist', $row['assessment']->id) }}"
                                                       class="table-action-btn {{ $row['submitted'] ? 'btn-view' : 'btn-verify' }}"
                                                       title="{{ $row['submitted'] ? 'View Checklist' : 'Go to Checklist' }}">
                                                        <i class="fa {{ $row['submitted'] ? 'fa-eye' : 'fa-circle-check' }}"></i>
                                                    </a>
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

        {{-- Project-wise Progress + Recent Assessment Activity --}}
        <div class="row g-3 mt-1">
            <div class="col-lg-6">
                <div class="card custom-card h-100">
                    <div class="card-header section-header">
                        <h5 class="mb-0"><i class="fa fa-chart-bar me-2"></i>Project-wise Progress</h5>
                    </div>
                    <div class="card-body">
                        @if ($projectProgress->isEmpty())
                            <p class="text-muted mb-0">No projects assigned to you yet.</p>
                        @else
                            @foreach ($projectProgress as $row)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-medium text-truncate me-2" style="max-width:220px;font-size:.85rem"
                                            title="{{ $row['project']?->project_name }}">
                                            {{ $row['project']?->project_name ?? '—' }}
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
                    <div class="card-body p-0">
                        @if ($recentAssessmentActivity->isEmpty())
                            <p class="text-muted p-3 mb-0">No activity recorded yet.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>Assessment</th>
                                            <th>Item</th>
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
                                                <td class="text-truncate" style="max-width:140px;font-size:.82rem"
                                                    title="{{ $act->checklistItem?->checklist_item }}">
                                                    {{ $act->checklistItem?->checklist_item ?? '—' }}
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
</style>
@endpush
