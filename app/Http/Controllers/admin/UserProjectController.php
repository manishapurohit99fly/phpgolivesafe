<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistCategory;
use App\Models\Project;
use App\Models\ProjectChecklist;
use App\Services\ProjectReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserProjectController extends Controller
{
    public function __construct(protected ProjectReportService $reportService) {}

    // ------------------------------------------------------------------
    // User Dashboard
    // ------------------------------------------------------------------

    public function dashboard(): View
    {
        $userId = auth('admin')->id();

        $assignedProjects = Project::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->withCount([
                'checklists',
                'checklists as completed_count' => fn ($q) => $q->where('is_checked', 1),
            ])
            ->get();

        $totalAssigned    = $assignedProjects->count();
        $verifiedProjects = $assignedProjects->filter(
            fn ($p) => $p->checklists_count > 0 && $p->checklists_count === $p->completed_count
        )->count();
        $pendingProjects  = $totalAssigned - $verifiedProjects;

        $projectIds     = $assignedProjects->pluck('id');
        $totalItems     = ProjectChecklist::whereIn('project_id', $projectIds)->count();
        $completedItems = ProjectChecklist::whereIn('project_id', $projectIds)->where('is_checked', 1)->count();
        $pendingItems   = $totalItems - $completedItems;
        $overallPercent = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

        $recentActivity = ProjectChecklist::where('checked_by', $userId)
            ->where('is_checked', 1)
            ->with([
                'project:id,project_name',
                'checklistItem:id,checklist_item',
            ])
            ->orderByDesc('checked_at')
            ->limit(5)
            ->get();

        $projectProgress = $assignedProjects->map(fn ($p) => [
            'project'   => $p,
            'total'     => $p->checklists_count,
            'completed' => $p->completed_count,
            'pending'   => $p->checklists_count - $p->completed_count,
            'percent'   => $p->checklists_count > 0
                ? round(($p->completed_count / $p->checklists_count) * 100)
                : 0,
        ])->values();

        return view('admin.user-projects.dashboard', compact(
            'totalAssigned', 'verifiedProjects', 'pendingProjects',
            'totalItems', 'completedItems', 'pendingItems', 'overallPercent',
            'recentActivity', 'projectProgress'
        ));
    }

    // ------------------------------------------------------------------
    // My Assigned Projects
    // ------------------------------------------------------------------

    public function index(): View
    {
        return view('admin.user-projects.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        $userId = auth('admin')->id();

        $query = Project::whereHas('users', fn ($q) => $q->where('user_id', $userId));

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

        $recordsTotal    = Project::whereHas('users', fn ($q) => $q->where('user_id', $userId))->count();
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
                'items'    => $grouped->get($cat->id, collect()),
            ]);

        return view('admin.user-projects.verify', compact(
            'project', 'categories', 'total', 'completed', 'pending', 'percent'
        ));
    }

    public function saveVerification(Request $request, int $id): JsonResponse
    {
        $userId  = auth('admin')->id();
        $project = Project::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->findOrFail($id);

        $items = $request->input('items', []);
        $now   = now();

        DB::transaction(function () use ($project, $items, $userId, $now, $request) {
            foreach ($items as $pcId => $data) {
                $pc = ProjectChecklist::where('id', $pcId)
                    ->where('project_id', $project->id)
                    ->first();
                if (!$pc) continue;

                $isChecked = !empty($data['is_checked']) ? 1 : 0;
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

        $total     = ProjectChecklist::where('project_id', $project->id)->count();
        $completed = ProjectChecklist::where('project_id', $project->id)->where('is_checked', 1)->count();
        $pending   = $total - $completed;
        $percent   = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

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
        $userId   = auth('admin')->id();
        $projects = Project::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->orderBy('project_name')
            ->get(['id', 'project_name', 'client_name']);

        $selectedProjectId = $request->filled('project_id') ? $request->project_id : null;

        return view('admin.user-reports.index', compact('projects', 'selectedProjectId'));
    }

    public function reportLoad(Request $request): Response
    {
        if (!$request->filled('project_id')) {
            return response('');
        }

        $userId  = auth('admin')->id();
        $id      = decrypt_id($request->project_id);
        $project = Project::whereHas('users', fn ($q) => $q->where('user_id', $userId))->find($id);

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


