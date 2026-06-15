<?php

namespace App\Http\Controllers;

use App\Models\SharedReport;
use App\Services\ProjectReportService;
use Illuminate\View\View;

class PublicReportController extends Controller
{
    public function __construct(protected ProjectReportService $reportService) {}

    public function show(string $token): View
    {
        $shared  = SharedReport::where('unique_token', $token)->firstOrFail();
        $project = $shared->project;

        abort_if(!$project, 404);

        $reportData = $this->reportService->getReportData($project);

        return view('public.project-report', compact('reportData'));
    }
}
