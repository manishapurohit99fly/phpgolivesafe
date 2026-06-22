@extends('admin.layout.index')
@section('admin-title', 'Edit Tech Stack')

@section('content')
<div class="container-fluid">
    <div class="page-content-wrapper">

        <div class="page-title-row d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="page-title-row-text">
                <h4>Edit Tech Stack</h4>
                <p class="page-title-row-sub">Update technology stack details.</p>
            </div>
            <a href="{{ route('admin.tech-stack.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card custom-card">
            <div class="card-body">
                <form id="techStackForm"
                    data-action="{{ route('admin.tech-stack.update', $techStack->id) }}"
                    data-mode="edit"
                    data-redirect-back="{{ route('admin.tech-stack.index') }}"
                    data-save-label="Update"
                    data-saving-label="Updating...">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control"
                                value="{{ $techStack->name }}" maxlength="100">
                            <div class="form-valid-error d-none" id="error-name"></div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control"
                                value="{{ $techStack->sort_order }}" min="0">
                            <div class="form-valid-error d-none" id="error-sort_order"></div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select select2">
                                <option value="1" {{ $techStack->status == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $techStack->status == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <div class="form-valid-error d-none" id="error-status"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <a href="{{ route('admin.tech-stack.index') }}" class="btn btn-outline-primary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4" id="submitBtn">Update</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/project.js') }}"></script>
@endpush
