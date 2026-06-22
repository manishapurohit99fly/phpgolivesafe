<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Project;
use App\Services\AssessmentReportService;
use App\Services\ProjectReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminProjectReportController extends Controller
{
    public function __construct(
        protected ProjectReportService    $reportService,
        protected AssessmentReportService $assessmentReportService,
    ) {}

    public function index(Request $request): View
    {
        $selectedProjectId    = $request->filled('project_id')    ? $request->project_id    : null;
        $selectedAssessmentId = $request->filled('assessment_id') ? $request->assessment_id : null;

        $selectedProject    = null;
        $selectedAssessment = null;

        if ($selectedProjectId) {
            $id = decrypt_id($selectedProjectId);
            $selectedProject = Project::find($id, ['id', 'project_name', 'client_name']);
        }

        if ($selectedAssessmentId) {
            $id = decrypt_id($selectedAssessmentId);
            $selectedAssessment = Assessment::find($id, ['id', 'name', 'project_id']);
        }

        return view('admin.project-reports.index', compact(
            'selectedProjectId', 'selectedProject',
            'selectedAssessmentId', 'selectedAssessment'
        ));
    }

    // Select2 AJAX — project search
    public function search(Request $request): JsonResponse
    {
        $term    = $request->get('q', '');
        $page    = max(1, (int) $request->get('page', 1));
        $perPage = 15;

        $query = Project::orderBy('project_name')->select('id', 'project_name', 'client_name');

        if ($term !== '') {
            $query->where(fn ($q) => $q
                ->where('project_name', 'like', "%{$term}%")
                ->orWhere('client_name', 'like', "%{$term}%")
            );
        }

        $total   = $query->count();
        $results = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'results'    => $results->map(fn ($p) => [
                'id'   => encrypt_id($p->id),
                'text' => $p->project_name . ($p->client_name ? ' (' . $p->client_name . ')' : ''),
            ]),
            'pagination' => ['more' => ($page * $perPage) < $total],
        ]);
    }

    // Assessments for a selected project (dropdown options)
    public function assessments(Request $request): JsonResponse
    {
        if (!$request->filled('project_id')) {
            return response()->json(['results' => []]);
        }

        $projectId = decrypt_id($request->project_id);

        $assessments = Assessment::where('project_id', $projectId)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'results' => $assessments->map(fn ($a) => [
                'id'   => encrypt_id($a->id),
                'text' => $a->name,
            ]),
        ]);
    }

    // Load project-level report (legacy — kept for backward compat)
    public function load(Request $request): Response
    {
        if (!$request->filled('project_id')) {
            return response('');
        }

        $id      = decrypt_id($request->project_id);
        $project = Project::find($id);

        if (!$project) {
            return response('');
        }

        $reportData = $this->reportService->getReportData($project);

        return response(view('partials.project-report', [
            'reportData' => $reportData,
            'isPublic'   => false,
        ])->render());
    }

    // Load assessment-level report
    public function assessmentLoad(Request $request): Response
    {
        if (!$request->filled('assessment_id')) {
            return response('');
        }

        $id         = decrypt_id($request->assessment_id);
        $assessment = Assessment::with('project')->find($id);

        if (!$assessment) {
            return response('');
        }

        $reportData = $this->assessmentReportService->getReportData($assessment);

        return response(view('partials.assessment-report', compact('reportData'))->render());
    }
}
