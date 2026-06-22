<?php

namespace App\Http\Controllers;

use App\Models\SharedReport;
use App\Services\AssessmentReportService;
use App\Services\ProjectReportService;
use Illuminate\View\View;

class PublicReportController extends Controller
{
    public function __construct(
        protected ProjectReportService    $projectReportService,
        protected AssessmentReportService $assessmentReportService,
    ) {}

    public function show(string $token): View
    {
        $shared = SharedReport::where('unique_token', $token)->firstOrFail();

        if ($shared->assessment_id) {
            $assessment = $shared->assessment;
            abort_if(!$assessment, 404);

            $reportData = $this->assessmentReportService->getReportData($assessment);

            return view('public.assessment-report', compact('reportData'));
        }

        $project = $shared->project;
        abort_if(!$project, 404);

        $reportData = $this->projectReportService->getReportData($project);

        return view('public.project-report', compact('reportData'));
    }
}
