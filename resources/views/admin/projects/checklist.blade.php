@extends('admin.layout.index')
@section('admin-title', 'Assign Checklist')

@section('content')
<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">

        {{-- Page Header --}}
        <div class="page-title-row d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h4 class="mb-0 ms-3">Assign Checklist</h4>
                <p class="text-muted small ms-3 mb-0 mt-1">
                    <i class="fa fa-diagram-project me-1"></i>{{ $project->project_name }}
                    @if($project->client_name)
                        &nbsp;&bull;&nbsp;{{ $project->client_name }}
                    @endif
                    @if($project->techStack)
                        &nbsp;&bull;&nbsp;
                        <span class="badge bg-primary-subtle text-primary">
                            <i class="fa fa-layer-group me-1"></i>{{ $project->techStack->name }}
                        </span>
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.project.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back to Projects
            </a>
        </div>
 
        <form id="checklistForm" class="whiteBg"
            data-action="{{ enroute('admin.project.checklist.save', $project->id) }}"
            data-redirect="{{ route('admin.project.index') }}"
            data-save-label="Save Assignment"
            data-saving-label="Saving...">
            @csrf

            {{-- Toolbar --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

                <div class="d-flex align-items-center gap-3">
                    <span id="selectionCounter" class="text-muted small">
                        <i class="fa fa-list-check me-1"></i>
                        <span id="selectedCount">{{ count($assignedIds) }}</span>
                        / {{ $categories->sum(fn($c) => $c->items->count()) }}
                        selected
                    </span>

                    <span class="badge bg-light text-dark border">
                        {{ $categories->count() }} Categories
                    </span>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllBtn">
                        Select All
                    </button>

                    <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAllBtn">
                        Clear All
                    </button>
                </div> 
            </div>
            <hr class="my-3">

            {{-- Empty state when no categories match the tech stack --}}
            @if($categories->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa fa-layer-group fa-2x mb-3 d-block"></i>
                    @if($project->tech_stack_id)
                        No checklist items found for the <strong>{{ $project->techStack?->name }}</strong> tech stack.
                        <br>
                        <a href="{{ route('admin.tech-stack.index') }}" class="btn btn-sm btn-outline-primary mt-3">
                            Manage Tech Stacks
                        </a>
                    @else
                        No tech stack selected for this project.
                        <br>
                        <a href="{{ enroute('admin.project.edit', $project->id) }}" class="btn btn-sm btn-outline-primary mt-3">
                            Edit Project to Set Tech Stack
                        </a>
                    @endif
                </div>
            @endif

            {{-- Category Accordion --}}
            <div id="checklistAccordion" {{ $categories->isEmpty() ? 'style=display:none' : '' }}>
                @foreach($categories as $catIndex => $category)
                    @php
                        $categoryItemIds = $category->items->pluck('id')->toArray();
                        $assignedInCat   = count(array_intersect($categoryItemIds, $assignedIds));
                        $totalInCat      = count($categoryItemIds);
                        $allChecked      = $totalInCat > 0 && $assignedInCat === $totalInCat;
                        $someChecked     = $assignedInCat > 0 && !$allChecked;
                    @endphp

                    <div class="checklist-category-card mb-2">
                        {{-- Category Header --}}                        
                        <div class="checklist-cat-header d-flex align-items-center gap-3"
                                data-bs-toggle="collapse"
                                data-bs-target="#cat-{{ $category->id }}"
                                aria-expanded="false">

                            <div class="form-check mb-0" onclick="event.stopPropagation()">
                                <input class="form-check-input cat-select-all"
                                    type="checkbox"
                                    id="cat_all_{{ $category->id }}"
                                    data-cat="{{ $category->id }}"
                                    {{ $allChecked ? 'checked' : '' }}
                                    data-indeterminate="{{ $someChecked ? 'true' : 'false' }}">
                                <label class="form-check-label visually-hidden" for="cat_all_{{ $category->id }}">
                                    Select all in {{ $category->category_name }}
                                </label>
                            </div>

                            <div class="flex-grow-1 d-flex align-items-center justify-content-between">
                                <span class="cat-name fw-semibold">{{ $category->category_name }}</span>
                                <span class="badge bg-primary-subtle text-primary cat-badge" id="badge-{{ $category->id }}">
                                    {{ $assignedInCat }} / {{ $totalInCat }}
                                </span>
                            </div>
                            <i class="fa fa-chevron-down cat-chevron {{ $assignedInCat > 0 ? '' : '' }}" id="chevron-{{ $category->id }}"></i>
                        </div>
                        {{-- Category Items --}}                        
                        <div class="checklist-cat-body collapse" id="cat-{{ $category->id }}" data-bs-parent="#checklistAccordion">
                            @foreach($category->items as $item)
                                <div class="checklist-item-row d-flex align-items-center gap-3">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input item-checkbox"
                                            type="checkbox"
                                            name="checklist_items[]"
                                            value="{{ $item->id }}"
                                            id="item_{{ $item->id }}"
                                            data-cat="{{ $category->id }}"
                                            {{ in_array($item->id, $assignedIds) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="item_{{ $item->id }}">
                                            {{ $item->checklist_item }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Bottom Save --}}
            @if($categories->isNotEmpty())
            <div class="d-flex justify-content-end mt-5">
                <a href="{{ route('admin.project.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary" id="saveBtnBottom">
                    <i class="fa fa-floppy-disk me-1"></i>Save Assignment
                </button>
            </div>
            @endif

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/checklist.js') }}"></script>
@endpush
