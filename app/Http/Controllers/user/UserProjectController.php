<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentChecklist;
use App\Models\ChecklistCategory;
use App\Models\Project;
use App\Models\ProjectChecklist;
use App\Models\User;
use App\Services\AssessmentReportService;
use App\Services\ProjectReportService;
use App\Traits\Common_trait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserProjectController extends Controller
{
    use Common_trait;

    public function __construct(
        protected ProjectReportService    $reportService,
        protected AssessmentReportService $assessmentReportService,
    ) {}

    // ------------------------------------------------------------------
    // User Dashboard
    // ------------------------------------------------------------------

    public function dashboard(): View
    {
        $userId = auth('admin')->id();

        // Assessments directly assigned to this user
        $assignedAssessments = Assessment::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 1)
            ->with('project:id,project_name')
            ->withCount([
                'checklists',
                'checklists as completed_count' => fn ($q) => $q->where('is_checked', 1),
            ])
            ->get();

        $totalAssigned        = $assignedAssessments->count();
        $completedAssessments = $assignedAssessments->filter(fn ($a) => !is_null($a->submitted_at))->count();
        $pendingAssessments   = $totalAssigned - $completedAssessments;

        $totalItems     = $assignedAssessments->sum('checklists_count');
        $completedItems = $assignedAssessments->sum('completed_count');
        $overallPercent = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

        $assessmentProgress = $assignedAssessments->map(fn ($a) => [
            'assessment' => $a,
            'project'    => $a->project,
            'total'      => $a->checklists_count,
            'completed'  => $a->completed_count,
            'submitted'  => !is_null($a->submitted_at),
            'percent'    => $a->checklists_count > 0
                ? round(($a->completed_count / $a->checklists_count) * 100)
                : 0,
        ])->values();

        // Project-wise progress: group the user's assigned assessments by project
        $projectProgress = $assignedAssessments->groupBy('project_id')->map(function ($group) {
            $project   = $group->first()->project;
            $total     = $group->sum('checklists_count');
            $completed = $group->sum('completed_count');
            return [
                'project'   => $project,
                'total'     => $total,
                'completed' => $completed,
                'percent'   => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        })->sortByDesc('percent')->values();

        // Recent assessment activity checked by this user
        $assessmentIds = $assignedAssessments->pluck('id');
        $recentAssessmentActivity = AssessmentChecklist::whereIn('assessment_id', $assessmentIds)
            ->where('checked_by', $userId)
            ->where('is_checked', 1)
            ->with([
                'assessment:id,name,project_id',
                'assessment.project:id,project_name',
                'checklistItem:id,checklist_item',
            ])
            ->orderByDesc('checked_at')
            ->limit(5)
            ->get();

        return view('user.user-projects.dashboard', compact(
            'totalAssigned', 'completedAssessments', 'pendingAssessments',
            'totalItems', 'completedItems', 'overallPercent',
            'assessmentProgress', 'projectProgress', 'recentAssessmentActivity'
        ));
    }

    // ------------------------------------------------------------------
    // My Assigned Projects
    // ------------------------------------------------------------------

    public function index(): View
    {
        return view('user.user-projects.index');
    }

    public function projectList(Request $request): JsonResponse
    {
        $userId = auth('admin')->id();

        $query = Project::whereHas('assessments', fn ($q) => $q
            ->whereHas('users', fn ($u) => $u->where('user_id', $userId))
            ->where('status', 1)
        )->withCount([
            'assessments as user_assessments_count' => fn ($q) => $q
                ->whereHas('users', fn ($u) => $u->where('user_id', $userId))
                ->where('status', 1),
        ]);

        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $query->where(fn ($q) => $q
                ->where('project_name', 'like', "%{$kw}%")
                ->orWhere('client_name', 'like', "%{$kw}%")
            );
        }

        $perPage  = 10;
        $page     = max(1, (int) $request->get('page', 1));
        $total    = $query->count();
        $lastPage = (int) ceil($total / $perPage) ?: 1;
        $page     = min($page, $lastPage);

        $projects = $query->orderBy('project_name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $html = view('user.user-projects._accordion', compact('projects'))->render();

        return response()->json([
            'html'      => $html,
            'count'     => $projects->count(),
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => $lastPage,
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $userId = auth('admin')->id();

        $query = Project::whereHas('users', fn ($q) => $q->where('user_id', $userId))->where('status',1);

        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $query->where(fn ($q) => $q
                ->where('project_name', 'like', "%{$kw}%")
                ->orWhere('client_name', 'like', "%{$kw}%")
            );
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $recordsTotal    = Project::whereHas('users', fn ($q) => $q->where('user_id', $userId))->where('status',1)->count();
        $recordsFiltered = $query->count();

        $start  = (int) ($request->start  ?? 0);
        $length = (int) ($request->length ?? 10);

        $projects = $query->orderBy('project_name')->skip($start)->take($length)->get();

        $data = [];
        foreach ($projects as $index => $project) {
            $statusBadge = $project->status
                ? '<span class="badge bg-success-subtle text-success">Active</span>'
                : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>';

            $reportUrl = route('user.reports.index') . '?project_id=' . encrypt_id($project->id);

            $actions = table_action_icons(
                '<a href="' . enroute('user.project.verify', $project->id) . '" class="table-action-btn btn-verify" title="Verify Checklist"><i class="fa fa-circle-check" aria-hidden="true"></i></a>'
                . '<a href="' . enroute('user.project.report', $project->id). '" class="table-action-btn btn-report" title="Report"><i class="fa fa-chart-bar" aria-hidden="true"></i></a>'
            );

            $data[] = [
                'DT_RowIndex'  => $start + $index + 1,
                'project_name' => e($project->project_name),
                'client_name'  => e($project->client_name ?? '—'),
                'status'       => $statusBadge,
                'created_at'   => getDateInFormat($project->created_at),
                'action'       => $actions,
            ];
        }

        return response()->json([
            'draw'            => intval($request->draw),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    // ------------------------------------------------------------------
    // Verify (role-3 user)
    // ------------------------------------------------------------------

    public function verify(int $id): View
    {
        $userId  = auth('admin')->id();
        $project = Project::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->with('users:id,first_name,last_name')
            ->where('status',1)
            ->findOrFail($id);

        $checklists = ProjectChecklist::where('project_id', $project->id)
            ->with(['checklistItem.category', 'checkedBy'])
            ->get();

        $total     = $checklists->count();
        $completed = $checklists->where('is_checked', 1)->count();
        $pending   = $total - $completed;
        $percent   = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        $grouped = $checklists
            ->filter(fn ($pc) => $pc->checklistItem !== null)
            ->sortBy(fn ($pc) => $pc->checklistItem->category->sort_order ?? 0)
            ->groupBy(fn ($pc) => $pc->checklistItem->category_id);

        $categories = ChecklistCategory::whereIn('id', $grouped->keys())
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($cat) => [
                'category' => $cat,
                'items'    => $grouped->get($cat->id, collect())
                                      ->sortBy(fn ($pc) => $pc->checklistItem->sort_order ?? 0)
                                      ->values(),
            ]);

        return view('user.user-projects.verify', compact(
            'project', 'categories', 'total', 'completed', 'pending', 'percent'
        ));
    }

    public function saveVerification(Request $request, int $id): JsonResponse
    {
        $userId  = auth('admin')->id();
        $project = Project::whereHas('users', fn ($q) => $q->where('user_id', $userId))->where('status',1)
            ->findOrFail($id);

        // Load ALL items in strict sequential order
        $allPcs = ProjectChecklist::where('project_id', $project->id)
            ->with(['checklistItem.category'])
            ->get()
            ->filter(fn ($pc) => $pc->checklistItem !== null)
            ->sortBy(fn ($pc) => sprintf('%05d_%05d',
                $pc->checklistItem->category->sort_order ?? 0,
                $pc->checklistItem->sort_order ?? 0
            ))
            ->values();

        $requestItems = $request->input('items', []);

        // Build desired state (hidden inputs supply 0 for unchecked; default to 0 if missing)
        $desiredState = $allPcs->mapWithKeys(fn ($pc) => [
            $pc->id => !empty($requestItems[$pc->id]['is_checked'] ?? '') ? 1 : 0,
        ]);

        // Enforce sequential order: item N may only be checked if item N-1 is checked
        $prevChecked = true;
        foreach ($allPcs as $pc) {
            if ($desiredState[$pc->id] && !$prevChecked) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please complete checklist items in order. Each item must be completed before the next.',
                ]);
            }
            $prevChecked = (bool) $desiredState[$pc->id];
        }

        $now = now();

        DB::transaction(function () use ($allPcs, $desiredState, $userId, $now, $project, $request) {
            foreach ($allPcs as $pc) {
                $isChecked = $desiredState[$pc->id];
                $pc->update([
                    'is_checked' => $isChecked,
                    'checked_by' => $isChecked ? $userId : null,
                    'checked_at' => $isChecked ? $now : null,
                ]);
            }

            if ($request->has('deployment_notes')) {
                $project->update(['deployment_notes' => $request->deployment_notes]);
            }
        });

        $total     = $allPcs->count();
        $completed = $allPcs->filter(fn ($pc) => $desiredState[$pc->id])->count();
        $pending   = $total - $completed;
        $percent   = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
        
        if ($pending === 0) {
            $verifier  = auth('admin')->user();
            $admin     = User::where('role', 1)->first();

            if ($admin) {
                $this->sendDynamicEmail(
                    $admin->email,
                    'all_items_verified',
                    [
                        'admin_name'   => $admin->first_name,
                        'verifier_name'=> trim($verifier->first_name . ' ' . $verifier->last_name),
                        'project_name' => $project->project_name,
                        'verified_at'  => now()->format('d M Y h:i A'),
                        'project_url'  => route('admin.project.show', $project->id),
                    ]
                );
            }
        }
        return response()->json([
            'success' => true,
            'message' => __('messages.verification_saved'),
            'stats'   => compact('total', 'completed', 'pending', 'percent'),
        ]);
    }

    // ------------------------------------------------------------------
    // My Project Reports
    // ------------------------------------------------------------------

    public function reportIndex(Request $request): View
    {
        $userId = auth('admin')->id();

        $selectedProjectId    = $request->filled('project_id') ? $request->project_id : null;
        $selectedAssessmentId = $request->filled('assessment_id') ? $request->assessment_id : null;

        $selectedProject    = null;
        $selectedAssessment = null;

        if ($selectedProjectId) {
            $id = decrypt_id($selectedProjectId);
            $selectedProject = Project::whereHas('users', fn ($q) => $q->where('user_id', $userId))
                ->where('status', 1)
                ->find($id, ['id', 'project_name', 'client_name']);
        }

        if ($selectedAssessmentId && $selectedProject) {
            $id = decrypt_id($selectedAssessmentId);
            $selectedAssessment = Assessment::whereHas('users', fn ($q) => $q->where('user_id', $userId))
                ->where('project_id', $selectedProject->id)
                ->where('status', 1)
                ->find($id, ['id', 'name']);
        }

        return view('user.user-reports.index', compact(
            'selectedProjectId', 'selectedProject',
            'selectedAssessmentId', 'selectedAssessment'
        ));
    }

    public function reportAssessments(Request $request): JsonResponse
    {
        if (!$request->filled('project_id')) {
            return response()->json(['results' => []]);
        }

        $userId    = auth('admin')->id();
        $projectId = decrypt_id($request->project_id);

        $assessments = Assessment::where('project_id', $projectId)
            ->whereHas('users', fn ($q) => $q->where('user_id', $userId))
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

    public function reportAssessmentLoad(Request $request): Response
    {
        if (!$request->filled('assessment_id')) {
            return response('');
        }

        $userId       = auth('admin')->id();
        $assessmentId = decrypt_id($request->assessment_id);

        $assessment = Assessment::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 1)
            ->with('project')
            ->find($assessmentId);

        if (!$assessment) {
            return response('');
        }

        $reportData = $this->assessmentReportService->getReportData($assessment);

        return response(view('partials.assessment-report', compact('reportData'))->render());
    }

    public function reportSearch(Request $request): JsonResponse
    {
        $userId  = auth('admin')->id();
        $term    = $request->get('q', '');
        $page    = max(1, (int) $request->get('page', 1));
        $perPage = 15;

        $query = Project::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 1)
            ->orderBy('project_name')
            ->select('id', 'project_name', 'client_name');

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

    public function reportLoad(Request $request): Response
    {
        if (!$request->filled('project_id')) {
            return response('');
        }

        $userId  = auth('admin')->id();
        $id      = decrypt_id($request->project_id);
        $project = Project::whereHas('users', fn ($q) => $q->where('user_id', $userId))->where('status',1)->find($id);

        if (!$project) {
            return response('');
        }

        $reportData = $this->reportService->getReportData($project);

        return response(view('partials.project-report', [
            'reportData' => $reportData,
            'isPublic'   => false,
        ])->render());
    }

    
}


