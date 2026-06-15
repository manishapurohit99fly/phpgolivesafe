@extends('admin.layout.index')
@section('admin-title', 'Project Details')

@section('content')
<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0 ms-3">Project Details</h4>
            <div class="d-flex gap-2">
                <a href="{{ enroute('admin.project.edit', $project->id) }}" class="btn btn-primary">
                    <i class="fa fa-pen me-1"></i> Edit
                </a>
                <a href="{{ route('admin.project.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-8">
                <div class="whiteBg">
                    <div class="row g-3">

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="fw-semibold mb-0">{{ $project->project_name }}</h5>
                                @if($project->status)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </div>
                            <hr>
                        </div>

                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Client Name</p>
                            <p class="fw-medium mb-0">{{ $project->client_name ?: '—' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Project URL</p>
                            @if($project->project_url)
                                <a href="{{ $project->project_url }}" target="_blank" rel="noopener noreferrer"
                                    class="text-primary text-decoration-none text-break">
                                    <i class="fa fa-arrow-up-right-from-square me-1 small"></i>{{ $project->project_url }}
                                </a>
                            @else
                                <p class="mb-0 text-muted">—</p>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Created Date</p>
                            <p class="mb-0">{{ getDateInFormat($project->created_at) }}</p>
                        </div>

                        <div class="col-md-6">
                            <p class="text-muted small mb-1">Last Updated</p>
                            <p class="mb-0">{{ getDateInFormat($project->updated_at) }}</p>
                        </div>

                        @if($project->project_description)
                        <div class="col-12">
                            <p class="text-muted small mb-1">Description</p>
                            <p class="mb-0">{!! nl2br(e($project->project_description)) !!}</p>
                        </div>
                        @endif

                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="whiteBg text-center">
                    <p class="text-muted small mb-2">Quick Actions</p>
                    <div class="d-grid gap-2">
                        <a href="{{ enroute('admin.project.checklist', $project->id) }}" class="btn btn-outline-primary">
                            <i class="fa fa-list-check me-2"></i>Assign Checklist
                        </a>
                        <a href="{{ enroute('admin.project.verify', $project->id) }}" class="btn btn-outline-success">
                            <i class="fa fa-circle-check me-2"></i>Verify Checklist
                        </a>
                        <a href="{{ enroute('admin.project.report', $project->id) }}" class="btn btn-outline-info">
                            <i class="fa fa-chart-bar me-2"></i>View Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
