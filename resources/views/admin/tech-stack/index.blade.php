@extends('admin.layout.index')
@section('admin-title', 'Tech Stack Master')

@section('content')
<div class="container-fluid">
    <div class="page-content-wrapper">

        <div class="page-title-row d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="page-title-row-text">
                <h4>Tech Stack Master</h4>
                <p class="page-title-row-sub">Manage technology stacks used for checklist templates.</p>
            </div>
            <a href="{{ route('admin.tech-stack.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> Add Tech Stack
            </a>
        </div>

        <div class="card custom-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Name</th>
                                <th>Categories</th>
                                <th>Projects</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($techStacks as $i => $ts)
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td class="fw-medium">{{ $ts->name }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">
                                            {{ $ts->categories()->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            {{ $ts->projects()->count() }}
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ $ts->sort_order }}</td>
                                    <td>
                                        <label class="switch">
                                            <input type="checkbox" {{ $ts->status ? 'checked' : '' }}
                                                onchange="updateStatus('{{ encrypt_id($ts->id) }}','TechStack','{{ route('admin.updateStatus') }}',this)">
                                            <span class="slider-table"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.tech-stack.edit', $ts->id) }}"
                                               class="table-action-btn btn-edit" title="Edit">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <button type="button"
                                                class="table-action-btn btn-delete"
                                                title="Delete"
                                                onclick="deleteTechStack('{{ encrypt_id($ts->id) }}')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No tech stacks found. <a href="{{ route('admin.tech-stack.create') }}">Add one</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteTechStack(encId) {
    if (!confirm('Delete this Tech Stack? Checklist categories will be unlinked but not deleted.')) return;
    $.post('{{ route('admin.tech-stack.destroy') }}', {
        _token: '{{ csrf_token() }}',
        id: encId,
    }).done(function(res) {
        if (res.status === 'success') {
            toastr.success(res.message);
            setTimeout(() => location.reload(), 800);
        } else {
            toastr.error(res.message);
        }
    });
}
</script>
@endpush
