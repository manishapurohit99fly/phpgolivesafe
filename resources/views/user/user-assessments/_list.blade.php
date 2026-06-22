@if($assessments->isEmpty())
<div class="text-center py-4 text-muted small">
    <i class="fa fa-clipboard fa-lg mb-2 d-block"></i>
    No assessments assigned to you for this project.
</div>
@else
<div class="table-responsive">
    <table class="table inner-table align-middle mb-0">
        <thead>
            <tr>
                <th>#</th>
                <th>Assessment Name</th>
                <th>Progress</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assessments as $i => $assessment)
            @php
                $total     = $assessment->checklists_count ?? 0;
                $completed = $assessment->completed_count  ?? 0;
                $pct       = $total > 0 ? (int) round($completed / $total * 100) : 0;
            @endphp
            <tr>
                <td class="text-muted">{{ $i + 1 }}</td>
                <td>
                    <span class="fw-medium">{{ $assessment->name }}</span>
                    @if($assessment->description)
                        <div class="text-muted small text-truncate" style="max-width:260px">{{ $assessment->description }}</div>
                    @endif
                </td>
                <td style="min-width:130px">
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height:6px;border-radius:4px;">
                            <div class="progress-bar {{ $pct < 40 ? 'bg-danger' : ($pct < 75 ? 'bg-warning' : 'bg-success') }}"
                                style="width:{{ $pct }}%"></div>
                        </div>
                        <span class="text-muted small">{{ $completed }}/{{ $total }}</span>
                    </div>
                </td>
                <td>
                    @if($assessment->submitted_at)
                        <span class="badge bg-success">Submitted</span>
                    @elseif($assessment->status)
                        <span class="badge bg-success-subtle text-success">Active</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                    @endif
                </td>
                <td class="text-muted small">{{ $assessment->created_at->format('d M Y') }}</td>
                <td>
                    <div class="action-icons d-inline-flex align-items-center gap-1">
                        <a href="{{ enroute('user.assessment.checklist', $assessment->id) }}"
                            class="table-action-btn {{ $assessment->submitted_at ? 'btn-view' : 'btn-verify' }}"
                            title="{{ $assessment->submitted_at ? 'View Checklist (Read-only)' : 'Checklist' }}">
                            <i class="fa {{ $assessment->submitted_at ? 'fa-eye' : 'fa-circle-check' }}"></i>
                        </a>
                        <a href="{{ enroute('user.assessment.report', $assessment->id) }}"
                            class="table-action-btn btn-report" title="Report">
                            <i class="fa fa-chart-bar"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
