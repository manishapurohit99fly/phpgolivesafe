@extends('admin.layout.index')
@section('admin-title', 'Checklist')

@section('content')
<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">

        {{-- Page Header --}}
        <div class="page-title-row d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h4 class="mb-0 ms-3">Checklist</h4>
                <p class="text-muted small ms-3 mb-0 mt-1">
                    <i class="fa fa-clipboard-list me-1"></i>{{ $assessment->name }}
                    @if($assessment->project) &nbsp;&bull;&nbsp;<i class="fa fa-diagram-project me-1"></i>{{ $assessment->project->project_name }} @endif
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('user.project.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i>Back to Projects
                </a>
            </div>
        </div>

        {{-- Submitted Banner --}}
        @if($isSubmitted)
        <div class="alert alert-success d-flex align-items-center gap-3 mb-3" role="alert">
            <i class="fa fa-circle-check fa-xl"></i>
            <div>
                <strong>Assessment Submitted</strong>
                <div class="small">Submitted on {{ $assessment->submitted_at->format('d M Y, h:i A') }}. This assessment is now read-only.</div>
            </div>
        </div>
        @endif

        {{-- Stats Row --}}
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="verify-stat-card">
                    <div class="verify-stat-number" id="stat-total">{{ $total }}</div>
                    <div class="verify-stat-label">Total Items</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="verify-stat-card success">
                    <div class="verify-stat-number text-success" id="stat-completed">{{ $completed }}</div>
                    <div class="verify-stat-label">Completed</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="verify-stat-card warning">
                    <div class="verify-stat-number text-warning" id="stat-pending">{{ $pending }}</div>
                    <div class="verify-stat-label">Pending</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="verify-stat-card info">
                    <div class="verify-stat-number text-primary" id="stat-percent">{{ $percent }}%</div>
                    <div class="verify-stat-label">Completion</div>
                </div>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="whiteBg mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small text-muted fw-medium">Deployment Readiness</span>
                <span class="small fw-semibold" id="progress-label">{{ $percent }}%</span>
            </div>
            <div class="progress" style="height:10px;border-radius:8px;">
                <div class="progress-bar
                    @if($percent < 40) bg-danger
                    @elseif($percent < 75) bg-warning
                    @else bg-success @endif"
                    id="progress-bar"
                    role="progressbar"
                    style="width:{{ $percent }}%;border-radius:8px;"
                    aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </div>

        {{-- Assessment Info --}}
        <div class="whiteBg mb-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <p class="text-muted small mb-1">Assessment</p>
                    <p class="fw-semibold mb-0">{{ $assessment->name }}</p>
                </div>
                @if($assessment->project)
                <div class="col-md-3">
                    <p class="text-muted small mb-1">Project</p>
                    <p class="mb-0">{{ $assessment->project->project_name }}</p>
                </div>
                @endif
                <div class="col-md-3">
                    <p class="text-muted small mb-1">Status</p>
                    @if($isSubmitted)
                        <span class="badge bg-success">Submitted</span>
                    @elseif($assessment->status)
                        <span class="badge bg-success-subtle text-success">Active</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                    @endif
                </div>
                @if($assessment->users->count())
                <div class="col-md-3">
                    <p class="text-muted small mb-1">Assigned Verifiers</p>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($assessment->users as $u)
                            <span class="badge bg-primary-subtle text-primary">{{ trim($u->first_name . ' ' . $u->last_name) }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($total === 0)
            <div class="whiteBg text-center py-5">
                <i class="fa fa-list-check fa-3x text-muted mb-3 d-block"></i>
                <p class="text-muted mb-0">No checklist items have been assigned to this assessment yet.</p>
            </div>

        @elseif($isSubmitted)
        {{-- ── READ-ONLY VIEW ──────────────────────────────────────────── --}}
        @foreach($categories as $group)
            @php
                $cat      = $group['category'];
                $catItems = $group['items'];
                $catTotal = $catItems->count();
                $catDone  = $catItems->where('is_checked', 1)->count();
                $catPct   = $catTotal > 0 ? (int) round(($catDone / $catTotal) * 100) : 0;
            @endphp
            <div class="verify-category-card mb-3" data-cat-id="{{ $cat->id }}">
                <div class="verify-cat-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa fa-chevron-down verify-chevron open" id="vchevron-{{ $cat->id }}"></i>
                        <span class="cat-name fw-semibold">{{ $cat->category_name }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="verify-mini-progress">
                            <div class="verify-mini-bar
                                @if($catPct < 40) bg-danger
                                @elseif($catPct < 75) bg-warning
                                @else bg-success @endif"
                                style="width:{{ $catPct }}%"></div>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $catDone }} / {{ $catTotal }}</span>
                    </div>
                </div>
                <div class="verify-cat-body open" id="vcat-{{ $cat->id }}">
                    @foreach($catItems as $ac)
                    @php
                        $checkedByName = $ac->checkedBy
                            ? trim($ac->checkedBy->first_name . ' ' . $ac->checkedBy->last_name)
                            : null;
                    @endphp
                    <div class="verify-item-row {{ $ac->is_checked ? 'is-done' : '' }}" id="row-{{ $ac->id }}">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="verify-check-wrap mt-1">
                                @if($ac->is_checked)
                                    <i class="fa fa-circle-check text-success" style="font-size:1.1rem"></i>
                                @else
                                    <i class="fa fa-circle-xmark text-secondary" style="font-size:1.1rem"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="verify-item-label mb-0">{{ $ac->checklistItem->checklist_item }}</span>
                                    @if($ac->is_checked)
                                        <span class="badge bg-success-subtle text-success"><i class="fa fa-circle-check me-1"></i>Completed</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary"><i class="fa fa-circle-xmark me-1"></i>Not Completed</span>
                                    @endif
                                </div>
                                @if($ac->is_checked && $checkedByName)
                                <div class="verify-meta">
                                    <i class="fa fa-user-check me-1"></i>{{ $checkedByName }}
                                    @if($ac->checked_at)
                                        &nbsp;&bull;&nbsp;<i class="fa fa-calendar-check me-1"></i>{{ $ac->checked_at->format('d M Y, h:i A') }}
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @else
        {{-- ── EDITABLE FORM (sequential) ──────────────────────────────── --}}
        <form id="verifyForm"
            data-action="{{ enroute('user.assessment.checklist.save', $assessment->id) }}"
            data-save-label="Save Progress"
            data-saving-label="Saving..."
            data-sequential="1">
            @csrf

            @php $prevChecked = true; @endphp

            @foreach($categories as $group)
                @php
                    $cat      = $group['category'];
                    $catItems = $group['items'];
                    $catTotal = $catItems->count();
                    $catDone  = $catItems->where('is_checked', 1)->count();
                    $catPct   = $catTotal > 0 ? (int) round(($catDone / $catTotal) * 100) : 0;
                @endphp

                <div class="verify-category-card mb-3" data-cat-id="{{ $cat->id }}">
                    <div class="verify-cat-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa fa-chevron-down verify-chevron open" id="vchevron-{{ $cat->id }}"></i>
                            <span class="cat-name fw-semibold">{{ $cat->category_name }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="verify-mini-progress">
                                <div class="verify-mini-bar
                                    @if($catPct < 40) bg-danger
                                    @elseif($catPct < 75) bg-warning
                                    @else bg-success @endif"
                                    id="catbar-{{ $cat->id }}" style="width:{{ $catPct }}%">
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border cat-badge" id="catbadge-{{ $cat->id }}">
                                {{ $catDone }} / {{ $catTotal }}
                            </span>
                        </div>
                    </div>

                    <div class="verify-cat-body open" id="vcat-{{ $cat->id }}">
                        @foreach($catItems as $ac)
                            @php
                                $checkedByName = $ac->checkedBy
                                    ? trim($ac->checkedBy->first_name . ' ' . $ac->checkedBy->last_name)
                                    : null;
                                $isLocked  = !$ac->is_checked && !$prevChecked;
                                $prevChecked = (bool) $ac->is_checked;
                            @endphp

                            <div class="verify-item-row {{ $ac->is_checked ? 'is-done' : ($isLocked ? 'is-locked' : '') }}"
                                id="row-{{ $ac->id }}" data-cat="{{ $cat->id }}">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="verify-check-wrap mt-1">
                                        <input type="hidden" name="items[{{ $ac->id }}][is_checked]" value="0">
                                        <input class="form-check-input verify-checkbox"
                                            type="checkbox"
                                            name="items[{{ $ac->id }}][is_checked]"
                                            value="1"
                                            id="vc_{{ $ac->id }}"
                                            data-pc-id="{{ $ac->id }}"
                                            data-cat="{{ $cat->id }}"
                                            {{ $ac->is_checked ? 'checked' : '' }}
                                            {{ $isLocked ? 'disabled' : '' }}>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <label class="verify-item-label mb-0 {{ $isLocked ? 'text-muted' : '' }}" for="vc_{{ $ac->id }}">
                                                {{ $ac->checklistItem->checklist_item }}
                                            </label>
                                            @if($ac->is_checked)
                                                <span class="badge bg-success-subtle text-success verify-badge" id="badge-{{ $ac->id }}">
                                                    <i class="fa fa-circle-check me-1"></i>Completed
                                                </span>
                                            @elseif($isLocked)
                                                <span class="badge bg-secondary-subtle text-secondary verify-badge" id="badge-{{ $ac->id }}">
                                                    <i class="fa fa-lock me-1"></i>Locked
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning verify-badge" id="badge-{{ $ac->id }}">
                                                    <i class="fa fa-clock me-1"></i>Pending
                                                </span>
                                            @endif
                                        </div>
                                        @if($ac->is_checked && $checkedByName)
                                        <div class="verify-meta" id="meta-{{ $ac->id }}">
                                            <i class="fa fa-user-check me-1"></i>{{ $checkedByName }}
                                            @if($ac->checked_at)
                                                &nbsp;&bull;&nbsp;<i class="fa fa-calendar-check me-1"></i>{{ $ac->checked_at->format('d M Y, h:i A') }}
                                            @endif
                                        </div>
                                        @elseif($isLocked)
                                        <div class="verify-meta" id="meta-{{ $ac->id }}">
                                            <i class="fa fa-circle-info me-1"></i>Complete the previous item to unlock this one.
                                        </div>
                                        @else
                                        <div class="verify-meta d-none" id="meta-{{ $ac->id }}"></div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Footer Actions --}}
            <div class="whiteBg d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small">
                    <i class="fa fa-list-ol me-1"></i>Items must be completed in order. Unchecking an item will reset all items after it.
                </span>
                <div class="d-flex gap-2">
                    <a href="{{ route('user.project.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="button" class="btn btn-primary" id="saveVerifyBtn">
                        <i class="fa fa-floppy-disk me-1"></i>Save
                    </button>
                </div>
            </div>

        </form>
        @endif

    </div>
</div>
@endsection

@push('styles')
<style>
.verify-stat-card { background:#fff; border:1px solid #e9ecef; border-radius:10px; padding:18px 20px; text-align:center; }
.verify-stat-number { font-size:1.9rem; font-weight:700; line-height:1; margin-bottom:4px; }
.verify-stat-label { font-size:0.78rem; color:#6c757d; text-transform:uppercase; letter-spacing:.05em; }
.verify-category-card { background:#fff; border:1px solid #e9ecef; border-radius:10px; overflow:hidden; }
.verify-cat-header { padding:14px 20px; background:#f8f9fa; border-bottom:1px solid #e9ecef; cursor:pointer; user-select:none; }
.verify-cat-header:hover { background:#eef2ff; }
.cat-name { font-size:0.95rem; }
.verify-chevron { color:#6c757d; font-size:0.8rem; transition:transform .25s ease; }
.verify-chevron.open { transform:rotate(0deg); }
.verify-chevron:not(.open) { transform:rotate(-90deg); }
.verify-cat-body { display:none; }
.verify-cat-body.open { display:block; }
.verify-mini-progress { width:80px; height:6px; background:#e9ecef; border-radius:4px; overflow:hidden; }
.verify-mini-bar { height:100%; border-radius:4px; transition:width .4s ease; }
.verify-item-row { padding:16px 20px; border-bottom:1px solid #f1f3f5; transition:background .15s; }
.verify-item-row:last-child { border-bottom:none; }
.verify-item-row:hover { background:#fafbfc; }
.verify-item-row.is-done { background:#f0fdf4; }
.verify-item-row.is-done:hover { background:#dcfce7; }
.verify-item-row.is-locked { opacity:.55; background:#f8f9fa; pointer-events:none; }
.verify-check-wrap .form-check-input { width:1.25em; height:1.25em; cursor:pointer; }
.verify-check-wrap .form-check-input:disabled { cursor:not-allowed; }
.verify-item-label { font-size:0.9rem; font-weight:500; color:#374151; cursor:pointer; }
.verify-meta { font-size:0.78rem; color:#6b7280; margin-top:2px; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/verify.js') }}"></script>
@if(!$isSubmitted)
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    $('#saveVerifyBtn').on('click', function () {
        Swal.fire({
            title: 'Submit Verification?',
            html: 'Are you sure you want to submit this verification?<br><span class="text-muted small">Once submitted, the checklist cannot be modified.</span>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-floppy-disk me-1"></i>&nbsp;Yes, Submit',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#0d6efd',
            reverseButtons: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var $btn = $('#saveVerifyBtn');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Submitting...');

            $.ajax({
                url:  $('#verifyForm').data('action'),
                type: 'POST',
                data: $('#verifyForm').serialize(),
                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            title: 'Submitted!',
                            text: res.message || 'Verification submitted successfully.',
                            icon: 'success',
                            confirmButtonColor: '#0d6efd',
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        toastr.error(res.message || 'Something went wrong.');
                        $btn.prop('disabled', false).html('<i class="fa fa-floppy-disk me-1"></i>Save');
                    }
                },
                error: function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'An error occurred.';
                    toastr.error(msg);
                    $btn.prop('disabled', false).html('<i class="fa fa-floppy-disk me-1"></i>Save');
                }
            });
        });
    });
});
</script>
@endif
@endpush
