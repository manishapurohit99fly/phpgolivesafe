<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminProjectReportController extends Controller
{
    public function __construct(protected ProjectReportService $reportService) {}

    public function index(Request $request): View
    {
        $projects = Project::orderBy('project_name')->get(['id', 'project_name', 'client_name']);

        $selectedProjectId = $request->filled('project_id') ? $request->project_id : null;

        return view('admin.project-reports.index', compact('projects', 'selectedProjectId'));
    }

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

        return response(view('admin.partials.project-report', [
            'reportData' => $reportData,
            'isPublic'   => false,
        ])->render());
    }
}
