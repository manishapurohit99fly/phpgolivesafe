@extends('public.layout')
@section('page-title', $reportData['project']->project_name . ' — Deployment Report')

@section('content')
<div class="mb-3">
    <h4 class="fw-semibold mb-1">Deployment Checklist Report</h4>
    <p class="text-muted small mb-0">Shared read-only view &nbsp;&bull;&nbsp; Generated {{ now()->format('d M Y') }}</p>
</div>

@include('admin.partials.project-report', ['reportData' => $reportData, 'isPublic' => true])
@endsection

@push('styles')
{{-- styles injected by partial via @once/@push --}}
@endpush
