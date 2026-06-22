@extends('admin.layout.index')
@section('admin-title', 'Edit Project')

@section('content')
<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-3 ms-3">Edit Project</h4>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="whiteBg">
                    <form id="projectForm"
                        data-action="{{ enroute('admin.project.update', $project->id) }}"
                        data-mode="edit"
                        data-redirect-back="{{ route('admin.project.index') }}"
                        data-save-label="Update Project"
                        data-saving-label="Updating...">
                        @csrf

                        <div class="row mb-3">

                            <div class="col-md-8 mb-3">
                                <label for="project_name" class="form-label">Project Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control no-leading-space"
                                    name="project_name" id="project_name"
                                    value="{{ $project->project_name }}" maxlength="255">
                                <div class="form-valid-error d-none" id="error-project_name"></div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select select2">
                                    <option value="1" {{ $project->status == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $project->status == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <div class="form-valid-error d-none" id="error-status"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="client_name" class="form-label">Client Name</label>
                                <input type="text" class="form-control no-leading-space"
                                    name="client_name" id="client_name"
                                    value="{{ $project->client_name }}" maxlength="255">
                                <div class="form-valid-error d-none" id="error-client_name"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="project_url" class="form-label">Project URL</label>
                                <input type="url" class="form-control"
                                    name="project_url" id="project_url"
                                    value="{{ $project->project_url }}" maxlength="500">
                                <div class="form-valid-error d-none" id="error-project_url"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tech_stack_id" class="form-label">
                                    Tech Stack <span class="text-danger">*</span>
                                </label>
                                <select name="tech_stack_id" id="tech_stack_id" class="form-select select2">
                                    <option value="">-- Select Tech Stack --</option>
                                    @foreach($techStacks as $ts)
                                        <option value="{{ $ts->id }}"
                                            {{ $project->tech_stack_id == $ts->id ? 'selected' : '' }}>
                                            {{ $ts->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted">
                                    Changing tech stack will filter the checklist to the new stack's items.
                                </div>
                                <div class="form-valid-error d-none" id="error-tech_stack_id"></div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="assigned_users" class="form-label">
                                    <i class="fa fa-users me-1 text-primary"></i>Assigned Verification Users
                                </label>
                                <select name="assigned_users[]" id="assigned_users"
                                    class="form-select select2-multi" multiple
                                    data-placeholder="Select verification users...">
                                    @foreach($verifiers as $v)
                                        <option value="{{ $v->id }}"
                                            {{ in_array($v->id, $assignedIds) ? 'selected' : '' }}>
                                            {{ trim($v->first_name . ' ' . $v->last_name) }}
                                            ({{ $v->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @if($verifiers->isEmpty())
                                    <div class="form-text text-muted">No verification users found.</div>
                                @endif
                                <div class="form-valid-error d-none" id="error-assigned_users"></div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="project_description" class="form-label">Project Description</label>
                                <textarea name="project_description" id="project_description"
                                    class="form-control" rows="3">{{ $project->project_description }}</textarea>
                                <div class="form-valid-error d-none" id="error-project_description"></div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-12 d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.project.index') }}" class="btn btn-outline-primary px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4" id="submitBtn">Update Project</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/project.js') }}"></script>
@endpush
