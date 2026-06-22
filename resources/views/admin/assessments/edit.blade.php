@extends('admin.layout.index')
@section('admin-title', 'Edit Assessment')

@section('content')
<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-3 ms-3">Edit Assessment</h4>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="whiteBg">
                    <form id="assessmentEditForm"
                        data-action="{{ enroute('admin.assessment.update', $assessment->id) }}"
                        data-redirect-back="{{ route('admin.project.index') }}"
                        data-save-label="Update Assessment"
                        data-saving-label="Updating...">
                        @csrf

                        <div class="row mb-3">

                            <div class="col-md-8 mb-3">
                                <label class="form-label">Assessment Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control no-leading-space"
                                    name="name" value="{{ $assessment->name }}" maxlength="255">
                                <div class="form-valid-error d-none" id="error-name"></div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select select2">
                                    <option value="1" {{ $assessment->status == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $assessment->status == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <div class="form-valid-error d-none" id="error-status"></div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ $assessment->description }}</textarea>
                                <div class="form-valid-error d-none" id="error-description"></div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Assign Verifier</label>
                                <select name="assigned_user" class="form-select select2"
                                    data-placeholder="Select a verifier...">
                                    <option value=""></option>
                                    @foreach($projectUsers as $v)
                                        <option value="{{ $v->id }}"
                                            {{ $assignedId == $v->id ? 'selected' : '' }}>
                                            {{ $v->first_name }} {{ $v->last_name }} ({{ $v->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @if($projectUsers->isEmpty())
                                    <small class="text-muted">No users assigned to this project yet.</small>
                                @endif
                                <div class="form-valid-error d-none" id="error-assigned_user"></div>
                            </div>

                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-2">
                            <a href="{{ route('admin.project.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fa fa-floppy-disk me-1"></i>Update Assessment
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var $form = $('#assessmentEditForm');
    if (!$form.length) return;

    $form.on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#submitBtn');
        $btn.prop('disabled', true).text($form.data('saving-label') || 'Saving...');
        $('.form-valid-error').addClass('d-none').text('');

        $.ajax({
            url:  $form.data('action'),
            type: 'POST',
            data: $form.serialize(),
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    setTimeout(function () {
                        window.location.href = res.redirect_url || $form.data('redirect-back');
                    }, 800);
                } else {
                    $btn.prop('disabled', false).text($form.data('save-label') || 'Save');
                    toastr.error(res.message || 'Something went wrong.');
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).text($form.data('save-label') || 'Save');
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (field, msgs) {
                        var key = field.replace(/\[.*\]/, '');
                        $('#error-' + key).text(msgs[0]).removeClass('d-none');
                    });
                } else {
                    toastr.error('Something went wrong.');
                }
            }
        });
    });
});
</script>
@endpush
