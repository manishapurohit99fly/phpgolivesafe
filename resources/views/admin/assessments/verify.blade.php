@extends('admin.layout.index')
@section('admin-title', 'Checklist Verification')

@section('content')
<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">

        <div class="page-title-row d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h4 class="mb-0 ms-3">Checklist Verification</h4>
                <p class="text-muted small ms-3 mb-0 mt-1">
                    <i class="fa fa-clipboard-list me-1"></i>{{ $assessment->name }}
                    &nbsp;&bull;&nbsp;<i class="fa fa-diagram-project me-1"></i>{{ $assessment->project->project_name }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ enroute('admin.assessment.checklist', $assessment->id) }}" class="btn btn-outline-primary">
                    <i class="fa fa-list-check me-1"></i>Manage Assignment
                </a>
                <a href="{{ route('admin.project.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i>Back to Projects
                </a>
            </div>
        </div>

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
                <div class="col-md-3">
                    <p class="text-muted small mb-1">Project</p>
                    <p class="mb-0">{{ $assessment->project->project_name }}</p>
                </div>
                <div class="col-md-3">
                    <p class="text-muted small mb-1">Status</p>
                    @if($assessment->status)
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
                <p class="text-muted mb-3">No checklist items have been assigned to this assessment yet.</p>
                <a href="{{ enroute('admin.assessment.checklist', $assessment->id) }}" class="btn btn-primary">
                    <i class="fa fa-list-check me-1"></i>Assign Checklist Items
                </a>
            </div>
        @else

        <form id="verifyForm"
            data-action="{{ enroute('admin.assessment.verify.save', $assessment->id) }}"
            data-save-label="Save Progress"
            data-saving-label="Saving...">
            @csrf

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
                            @php $checkedByName = $ac->checkedBy ? trim($ac->checkedBy->first_name . ' ' . $ac->checkedBy->last_name) : null; @endphp

                            <div class="verify-item-row {{ $ac->is_checked ? 'is-done' : '' }}"
                                id="row-{{ $ac->id }}" data-cat="{{ $cat->id }}">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="verify-check-wrap mt-1">
                                        <input class="form-check-input verify-checkbox"
                                            type="checkbox"
                                            name="items[{{ $ac->id }}][is_checked]"
                                            value="1"
                                            id="vc_{{ $ac->id }}"
                                            data-pc-id="{{ $ac->id }}"
                                            data-cat="{{ $cat->id }}"
                                            {{ $ac->is_checked ? 'checked' : '' }}>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <label class="verify-item-label mb-0" for="vc_{{ $ac->id }}">
                                                {{ $ac->checklistItem->checklist_item }}
                                            </label>
                                            @if($ac->is_checked)
                                                <span class="badge bg-success-subtle text-success verify-badge" id="badge-{{ $ac->id }}">
                                                    <i class="fa fa-circle-check me-1"></i>Completed
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

            {{-- Notes --}}
            <div class="whiteBg mb-3">
                <label class="form-label fw-medium">
                    <i class="fa fa-file-lines me-1 text-primary"></i>Deployment Verification Notes
                </label>
                <textarea name="deployment_notes" class="form-control" rows="4"
                    placeholder="Add deployment notes, sign-off comments, or observations...">{{ $assessment->description }}</textarea>
                <div class="form-text">Notes are visible in the assessment report.</div>
            </div>

            <div class="whiteBg d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    <i class="fa fa-info-circle me-1"></i>Partial completion is allowed. Save progress at any time.
                </span>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.project.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="saveVerifyBtn">
                        <i class="fa fa-floppy-disk me-1"></i>Save Progress
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
.verify-check-wrap .form-check-input { width:1.25em; height:1.25em; cursor:pointer; }
.verify-item-label { font-size:0.9rem; font-weight:500; color:#374151; cursor:pointer; }
.verify-meta { font-size:0.78rem; color:#6b7280; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/verify.js') }}"></script>
@endpush
