@extends('admin.layout.index')
@section('admin-title', 'My Dashboard')

@section('content')
<div class="container-fluid">
    <div class="page-content-wrapper">

        <div class="page-title-row">
            <div class="page-title-row-text">
                <h4>My Dashboard</h4>
                <p class="page-title-row-sub">Status and progress of your assigned projects at a glance.</p>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="{{ route('user.project.index') }}">
                        <div class="dash-item-icon">
                            <i class="fa fa-diagram-project fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h6>Total Assigned Projects</h6>
                            <p class="mb-0">{{ $totalAssigned }}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="{{ route('user.project.index') }}">
                        <div class="dash-item-icon">
                            <i class="fa fa-circle-check fa-2x text-success"></i>
                        </div>
                        <div>
                            <h6>Verified Projects</h6>
                            <p class="mb-0">{{ $verifiedProjects }}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="{{ route('user.project.index') }}">
                        <div class="dash-item-icon">
                            <i class="fa fa-clock fa-2x text-warning"></i>
                        </div>
                        <div>
                            <h6>Pending Verification</h6>
                            <p class="mb-0">{{ $pendingProjects }}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="{{ route('user.reports.index') }}">
                        <div class="dash-item-icon">
                            <i class="fa fa-percent fa-2x text-info"></i>
                        </div>
                        <div>
                            <h6>Overall Completion</h6>
                            <p class="mb-0">{{ $overallPercent }}%</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="{{ route('user.reports.index') }}">
                        <div class="dash-item-icon">
                            <i class="fa fa-list-check fa-2x text-success"></i>
                        </div>
                        <div>
                            <h6>Completed Items</h6>
                            <p class="mb-0">{{ $completedItems }}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="{{ route('user.reports.index') }}">
                        <div class="dash-item-icon">
                            <i class="fa fa-hourglass-half fa-2x text-secondary"></i>
                        </div>
                        <div>
                            <h6>Pending Items</h6>
                            <p class="mb-0">{{ $pendingItems }}</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-1">
            {{-- Recent Verification Activity --}}
            <div class="col-lg-5">
                <div class="card custom-card h-100">
                    <div class="card-header section-header">
                        <h5 class="mb-0"><i class="fa fa-history me-2"></i>Recent Verification Activity</h5>
                    </div>
                    <div class="card-body p-0">
                        @if ($recentActivity->isEmpty())
                            <p class="text-muted p-3 mb-0">No verification activity yet.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>Project</th>
                                            <th>Item</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentActivity as $activity)
                                            <tr>
                                                <td class="text-truncate" style="max-width:120px"
                                                    title="{{ $activity->project?->project_name }}">
                                                    {{ $activity->project?->project_name ?? '—' }}
                                                </td>
                                                <td class="text-truncate" style="max-width:180px"
                                                    title="{{ $activity->checklistItem?->checklist_item }}">
                                                    {{ $activity->checklistItem?->checklist_item ?? '—' }}
                                                </td>
                                                <td class="text-nowrap">
                                                    {{ $activity->checked_at ? $activity->checked_at->format('d M Y') : '—' }}
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

            {{-- Project-wise Checklist Progress --}}
            <div class="col-lg-7">
                <div class="card custom-card h-100">
                    <div class="card-header section-header">
                        <h5 class="mb-0"><i class="fa fa-chart-bar me-2"></i>Project-wise Checklist Progress</h5>
                    </div>
                    <div class="card-body">
                        @if ($projectProgress->isEmpty())
                            <p class="text-muted mb-0">No projects assigned yet.</p>
                        @else
                            @foreach ($projectProgress as $row)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <a href="{{ enroute('user.project.verify', $row['project']->id) }}"
                                           class="fw-medium text-truncate me-2 text-decoration-none"
                                           style="max-width:280px"
                                           title="{{ $row['project']->project_name }}">
                                            {{ $row['project']->project_name }}
                                        </a>
                                        <small class="text-muted text-nowrap">
                                            {{ $row['completed'] }}/{{ $row['total'] }}
                                            ({{ $row['percent'] }}%)
                                        </small>
                                    </div>
                                    <div class="progress" style="height:8px">
                                        <div class="progress-bar
                                            {{ $row['percent'] === 100 ? 'bg-success' : ($row['percent'] >= 50 ? 'bg-primary' : 'bg-warning') }}"
                                            role="progressbar"
                                            style="width: {{ $row['percent'] }}%"
                                            aria-valuenow="{{ $row['percent'] }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
