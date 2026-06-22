@extends('public.layout')
@section('page-title', $reportData['assessment']->name . ' — Assessment Report')

@section('content')
<div class="mb-3">
    <h4 class="fw-semibold mb-1">Assessment Report</h4>
    <p class="text-muted small mb-0">
        Shared read-only view &nbsp;&bull;&nbsp; Generated {{ now()->format('d M Y') }}
    </p>
</div>

@include('partials.assessment-report', ['reportData' => $reportData, 'isPublic' => true])
@endsection
