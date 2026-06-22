@foreach($projects as $project)
@php
    $encId    = encrypt_id($project->id);
    $editUrl  = enroute('admin.project.edit', $project->id);
    $destroyUrl = route('admin.project.destroy');
    $statusLabel = $project->status
        ? '<span class="badge bg-success-subtle text-success">Active</span>'
        : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>';
    $assessmentCount = $project->assessments_count ?? 0;
@endphp

<div class="project-accordion-item" data-project-id="{{ $project->id }}">
    {{-- Project row --}}
    <div class="project-row d-flex align-items-center justify-content-between gap-2 flex-wrap {{ $project->status ? 'status-active' : 'status-inactive' }}">
        <div class="d-flex align-items-center gap-3 flex-grow-1 min-width-0">
            <button class="btn btn-sm btn-light border px-2 py-1 toggle-project-btn"
                data-project-id="{{ $project->id }}"
                title="Expand / Collapse assessments">
                <i class="fa fa-chevron-right toggle-icon"></i>
            </button>
            <div class="min-width-0">
                <div class="project-name text-truncate">{{ $project->project_name }}</div>
                @if($project->client_name)
                    <div class="project-meta"><i class="fa fa-building me-1"></i>{{ $project->client_name }}</div>
                @endif
            </div>
            {!! $statusLabel !!}
            <span class="badge bg-light text-dark border" title="Assessments">
                <i class="fa fa-clipboard-list me-1"></i>{{ $assessmentCount }}
            </span>
        </div>

        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <button class="btn btn-sm btn-outline-primary add-assessment-btn"
                data-project-id="{{ $project->id }}"
                data-project-name="{{ e($project->project_name) }}">
                <i class="fa fa-plus me-1"></i>Add Assessment
            </button>
            <a href="{{ $editUrl }}" class="table-action-btn btn-edit" title="Edit Project">
                <i class="fa fa-pen"></i>
            </a>
            <button type="button" class="table-action-btn btn-delete" title="Delete Project"
                onclick="ProjectList.deleteProject('{{ $encId }}', '{{ $destroyUrl }}')">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </div>

    {{-- Assessment panel (collapsed by default) --}}
    <div class="assessment-collapse d-none" id="assessment-panel-{{ $project->id }}">
        <div class="assessment-panel" id="assessment-list-{{ $project->id }}" data-loaded="false">
            <div class="text-center py-3 text-muted small">
                <i class="fa fa-spinner fa-spin me-1"></i>Loading assessments...
            </div>
        </div>
    </div>
</div>
@endforeach
