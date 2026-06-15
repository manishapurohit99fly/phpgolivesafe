@extends('admin.layout.index')
@section('admin-title', 'Project Report')

@section('content')
<div class="container-fluid pt-3 bg"
    id="reportDashboard"
>
    @include('admin.partials.project-report')
  
</div>
@endsection


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
