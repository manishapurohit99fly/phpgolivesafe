@foreach($projects as $project)
@php
    $statusLabel = $project->status
        ? '<span class="badge bg-success-subtle text-success">Active</span>'
        : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>';
    $assessmentCount = $project->user_assessments_count ?? 0;
@endphp

<div class="project-accordion-item" data-project-id="{{ $project->id }}">
    <div class="project-row d-flex align-items-center gap-3 flex-wrap {{ $project->status ? 'status-active' : 'status-inactive' }}">
        <button class="btn btn-sm btn-light border px-2 py-1 toggle-project-btn"
            data-project-id="{{ $project->id }}"
            title="Expand / Collapse assessments">
            <i class="fa fa-chevron-right toggle-icon"></i>
        </button>
        <div class="flex-grow-1 min-width-0">
            <div class="project-name text-truncate">{{ $project->project_name }}</div>
            @if($project->client_name)
                <div class="project-meta"><i class="fa fa-building me-1"></i>{{ $project->client_name }}</div>
            @endif
        </div>
        {!! $statusLabel !!}
        <span class="badge bg-light text-dark border" title="My Assessments">
            <i class="fa fa-clipboard-list me-1"></i>{{ $assessmentCount }}
        </span>
    </div>

    <div class="assessment-collapse d-none" id="assessment-panel-{{ $project->id }}">
        <div class="assessment-panel" id="assessment-list-{{ $project->id }}" data-loaded="false">
            <div class="text-center py-3 text-muted small">
                <i class="fa fa-spinner fa-spin me-1"></i>Loading assessments...
            </div>
        </div>
    </div>
</div>
@endforeach
