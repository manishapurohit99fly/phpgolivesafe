@extends('admin.layout.index')
@section('admin-title', 'Add Project')

@section('content')
<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-3 ms-3">Add Project</h4>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="whiteBg">
                    <form id="projectForm"
                        data-action="{{ route('admin.project.store') }}"
                        data-mode="create"
                        data-redirect-back="{{ route('admin.project.index') }}"
                        data-save-label="Save Project"
                        data-saving-label="Saving...">
                        @csrf

                        <div class="row mb-3">

                            <div class="col-md-8 mb-3">
                                <label for="project_name" class="form-label">Project Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control no-leading-space"
                                    name="project_name" id="project_name"
                                    placeholder="Enter project name" maxlength="255">
                                <div class="form-valid-error d-none" id="error-project_name"></div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select select2">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                <div class="form-valid-error d-none" id="error-status"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="client_name" class="form-label">Client Name</label>
                                <input type="text" class="form-control no-leading-space"
                                    name="client_name" id="client_name"
                                    placeholder="Enter client name" maxlength="255">
                                <div class="form-valid-error d-none" id="error-client_name"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="project_url" class="form-label">Project URL</label>
                                <input type="url" class="form-control"
                                    name="project_url" id="project_url"
                                    placeholder="https://example.com" maxlength="500">
                                <div class="form-valid-error d-none" id="error-project_url"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tech_stack_id" class="form-label">
                                    Tech Stack <span class="text-danger">*</span>
                                </label>
                                <select name="tech_stack_id" id="tech_stack_id" class="form-select select2">
                                    <option value="">-- Select Tech Stack --</option>
                                    @foreach($techStacks as $ts)
                                        <option value="{{ $ts->id }}">{{ $ts->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted">
                                    Determines which checklist template items will be available.
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
                                        <option value="{{ $v->id }}">
                                            {{ trim($v->first_name . ' ' . $v->last_name) }}
                                            ({{ $v->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @if($verifiers->isEmpty())
                                    <div class="form-text text-muted">No verification users found. Add users with the Verification User role first.</div>
                                @endif
                                <div class="form-valid-error d-none" id="error-assigned_users"></div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="project_description" class="form-label">Project Description</label>
                                <textarea name="project_description" id="project_description"
                                    class="form-control" rows="3"
                                    placeholder="Enter project description (optional)"></textarea>
                                <div class="form-valid-error d-none" id="error-project_description"></div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-12 d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.project.index') }}" class="btn btn-outline-primary px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4" id="submitBtn">Save Project</button>
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
