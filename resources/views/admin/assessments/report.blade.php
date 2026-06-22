@extends('admin.layout.index')
@section('admin-title', 'Assessment Report')

@section('content')
@php
    $assessment     = $reportData['assessment'];
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
    $chartId        = 'assessmentChart_' . $assessment->id;
    $backUrl        = $backUrl ?? route('admin.project.index');
@endphp

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">

        <div class="page-title-row d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h4 class="mb-0 ms-3">Assessment Report</h4>
                <p class="text-muted small ms-3 mb-0 mt-1">
                    <i class="fa fa-clipboard-list me-1"></i>{{ $assessment->name }}
                    &nbsp;&bull;&nbsp;<i class="fa fa-diagram-project me-1"></i>{{ $project->project_name }}
                </p>
            </div>
            <div class="d-flex gap-2">
                @if($shareToken)
                <button type="button" class="btn btn-outline-primary btn-sm"
                    onclick="navigator.clipboard.writeText('{{ route('project.public.report', $shareToken) }}').then(function(){toastr.success('Share URL copied!')})">
                    <i class="fa fa-link me-1"></i>Copy Share URL
                </button>
                @endif
                <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>

        {{-- Stats cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card text-center border-0 shadow-sm p-3">
                    <div style="font-size:2rem;font-weight:700">{{ $total }}</div>
                    <div class="text-muted small text-uppercase">Total Items</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center border-0 shadow-sm p-3">
                    <div style="font-size:2rem;font-weight:700" class="text-success">{{ $completed }}</div>
                    <div class="text-muted small text-uppercase">Completed</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center border-0 shadow-sm p-3">
                    <div style="font-size:2rem;font-weight:700" class="text-warning">{{ $pending }}</div>
                    <div class="text-muted small text-uppercase">Pending</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center border-0 shadow-sm p-3">
                    <div style="font-size:2rem;font-weight:700" class="text-primary">{{ $percent }}%</div>
                    <div class="text-muted small text-uppercase">Completion</div>
                </div>
            </div>
        </div>

        {{-- Progress bar + chart --}}
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-medium">Overall Progress</span>
                        <span class="fw-bold">{{ $percent }}%</span>
                    </div>
                    <div class="progress mb-4" style="height:12px;border-radius:8px;">
                        <div class="progress-bar {{ $percent < 40 ? 'bg-danger' : ($percent < 75 ? 'bg-warning' : 'bg-success') }}"
                            style="width:{{ $percent }}%;border-radius:8px;"></div>
                    </div>
                    {{-- Category breakdown --}}
                    @foreach($categories as $cat)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $cat['category']->category_name }}</span>
                            <span class="text-muted">{{ $cat['completed'] }} / {{ $cat['total'] }}</span>
                        </div>
                        <div class="progress" style="height:6px;border-radius:4px;">
                            <div class="progress-bar {{ $cat['percent'] < 40 ? 'bg-danger' : ($cat['percent'] < 75 ? 'bg-warning' : 'bg-success') }}"
                                style="width:{{ $cat['percent'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 h-100 d-flex align-items-center justify-content-center">
                    <canvas id="{{ $chartId }}" style="max-width:200px;max-height:200px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Assessment info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <p class="text-muted small mb-1">Assessment</p>
                        <p class="fw-semibold mb-0">{{ $assessment->name }}</p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted small mb-1">Project</p>
                        <p class="mb-0">{{ $project->project_name }}@if($project->client_name) &nbsp;/&nbsp; {{ $project->client_name }}@endif</p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted small mb-1">Status</p>
                        @if($assessment->status)
                            <span class="badge bg-success-subtle text-success">Active</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted small mb-1">Last Verified</p>
                        <p class="mb-0 small">{{ $lastVerifiedAt ? $lastVerifiedAt->format('d M Y, h:i A') : '—' }}</p>
                    </div>
                    @if($assignedUsers->count())
                    <div class="col-12">
                        <p class="text-muted small mb-1">Assigned Verifiers</p>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($assignedUsers as $u)
                                <span class="badge bg-primary-subtle text-primary">{{ trim($u->first_name . ' ' . $u->last_name) }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @if($deployNotes)
                    <div class="col-12">
                        <p class="text-muted small mb-1">Deployment Notes</p>
                        <p class="mb-0 small" style="white-space:pre-wrap">{{ $deployNotes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Category breakdown table --}}
        @foreach($categories as $cat)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-light py-2 px-4 d-flex justify-content-between align-items-center">
                <span class="fw-semibold small">{{ $cat['category']->category_name }}</span>
                <span class="badge {{ $cat['percent'] >= 100 ? 'bg-success' : 'bg-light text-dark border' }}">
                    {{ $cat['completed'] }} / {{ $cat['total'] }}
                </span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm align-middle mb-0">
                    <tbody>
                        @foreach($cat['items'] as $ac)
                        <tr>
                            <td class="ps-4" style="width:40px">
                                @if($ac->is_checked)
                                    <i class="fa fa-circle-check text-success"></i>
                                @else
                                    <i class="fa fa-circle-xmark text-danger"></i>
                                @endif
                            </td>
                            <td>{{ $ac->checklistItem->checklist_item }}</td>
                            <td class="text-muted small text-end pe-4">
                                @if($ac->is_checked && $ac->checkedBy)
                                    <i class="fa fa-user me-1"></i>{{ trim($ac->checkedBy->first_name . ' ' . $ac->checkedBy->last_name) }}
                                    @if($ac->checked_at)
                                        &nbsp;&bull;&nbsp;{{ $ac->checked_at->format('d M Y') }}
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    var ctx = document.getElementById('{{ $chartId }}');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending'],
            datasets: [{
                data: [{{ $completed }}, {{ $pending }}],
                backgroundColor: ['#22c55e', '#f1f5f9'],
                borderWidth: 0,
            }]
        },
        options: {
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });
})();
</script>
@endpush
