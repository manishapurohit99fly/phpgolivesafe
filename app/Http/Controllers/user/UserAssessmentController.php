<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentChecklist;
use App\Models\ChecklistCategory;
use App\Models\Project;
use App\Models\User;
use App\Services\AssessmentReportService;
use App\Traits\Common_trait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserAssessmentController extends Controller
{
    use Common_trait;

    public function __construct(protected AssessmentReportService $reportService) {}

    // ------------------------------------------------------------------
    // My Assigned Assessments — listing page
    // ------------------------------------------------------------------

    public function index(): View
    {
        return view('user.user-assessments.index');
    }

    public function forProject(int $projectId): JsonResponse
    {
        $userId = auth('admin')->id();

        $project = Project::findOrFail($projectId);

        $assessments = Assessment::where('project_id', $projectId)
            ->whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 1)
            ->withCount([
                'checklists',
                'checklists as completed_count' => fn ($q) => $q->where('is_checked', 1),
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $html = view('user.user-assessments._list', compact('project', 'assessments'))->render();

        return response()->json(['html' => $html]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $userId = auth('admin')->id();

        $query = Assessment::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 1)
            ->with('project:id,project_name,client_name');

        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$kw}%")
                ->orWhereHas('project', fn ($p) => $p
                    ->where('project_name', 'like', "%{$kw}%")
                    ->orWhere('client_name', 'like', "%{$kw}%")
                )
            );
        }

        $baseQuery = Assessment::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 1);

        $recordsTotal    = $baseQuery->count();
        $recordsFiltered = $query->count();

        $start  = (int) ($request->start  ?? 0);
        $length = (int) ($request->length ?? 10);

        $assessments = $query
            ->withCount([
                'checklists',
                'checklists as completed_count' => fn ($q) => $q->where('is_checked', 1),
            ])
            ->orderBy('created_at', 'desc')
            ->skip($start)->take($length)
            ->get();

        $data = [];
        foreach ($assessments as $index => $assessment) {
            $total     = $assessment->checklists_count ?? 0;
            $completed = $assessment->completed_count  ?? 0;
            $pct       = $total > 0 ? (int) round($completed / $total * 100) : 0;

            $barClass = $pct < 40 ? 'bg-danger' : ($pct < 75 ? 'bg-warning' : 'bg-success');
            $progress  = '<div class="d-flex align-items-center gap-2" style="min-width:120px">'
                . '<div class="progress flex-grow-1" style="height:6px;border-radius:4px;">'
                . '<div class="progress-bar ' . $barClass . '" style="width:' . $pct . '%"></div>'
                . '</div>'
                . '<span class="small text-muted">' . $completed . '/' . $total . '</span>'
                . '</div>';

            $statusBadge = $assessment->submitted_at
                ? '<span class="badge bg-success">Submitted</span>'
                : ($assessment->status
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>');

            $isSubmitted = (bool) $assessment->submitted_at;
            $checklistIcon = $isSubmitted
                ? '<a href="' . enroute('user.assessment.checklist', $assessment->id) . '" class="table-action-btn btn-view" title="View Checklist (Read-only)"><i class="fa fa-eye" aria-hidden="true"></i></a>'
                : '<a href="' . enroute('user.assessment.checklist', $assessment->id) . '" class="table-action-btn btn-checklist" title="Checklist"><i class="fa fa-list-check" aria-hidden="true"></i></a>';

            $actions = table_action_icons(
                $checklistIcon
                . '<a href="' . enroute('user.assessment.report', $assessment->id) . '" class="table-action-btn btn-report" title="Report"><i class="fa fa-chart-bar" aria-hidden="true"></i></a>'
            );

            $data[] = [
                'DT_RowIndex'     => $start + $index + 1,
                'assessment_name' => e($assessment->name),
                'project_name'    => e($assessment->project->project_name ?? '—')
                    . ($assessment->project->client_name ? ' <span class="text-muted small">/ ' . e($assessment->project->client_name) . '</span>' : ''),
                'progress'        => $progress,
                'status'          => $statusBadge,
                'created_at'      => getDateInFormat($assessment->created_at),
                'action'          => $actions,
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
    // Checklist Verification (user's "Checklist" action — sequential)
    // ------------------------------------------------------------------

    public function verify(int $id): View
    {
        $userId     = auth('admin')->id();
        $assessment = Assessment::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 1)
            ->with(['project', 'users:id,first_name,last_name'])
            ->findOrFail($id);

        $checklists = AssessmentChecklist::where('assessment_id', $assessment->id)
            ->with(['checklistItem.category', 'checkedBy'])
            ->get();

        $total     = $checklists->count();
        $completed = $checklists->where('is_checked', 1)->count();
        $pending   = $total - $completed;
        $percent   = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        $grouped = $checklists
            ->filter(fn ($ac) => $ac->checklistItem !== null)
            ->sortBy(fn ($ac) => $ac->checklistItem->category->sort_order ?? 0)
            ->groupBy(fn ($ac) => $ac->checklistItem->category_id);

        $categories = ChecklistCategory::whereIn('id', $grouped->keys())
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($cat) => [
                'category' => $cat,
                'items'    => $grouped->get($cat->id, collect())
                                ->sortBy(fn ($ac) => $ac->checklistItem->sort_order ?? 0)
                                ->values(),
            ]);

        $isSubmitted = (bool) $assessment->submitted_at;

        return view('user.user-assessments.verify', compact(
            'assessment', 'categories', 'total', 'completed', 'pending', 'percent', 'isSubmitted'
        ));
    }

    public function submit(int $id): JsonResponse
    {
        $userId     = auth('admin')->id();
        $assessment = Assessment::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 1)
            ->findOrFail($id);

        if ($assessment->submitted_at) {
            return response()->json(['success' => false, 'message' => 'This assessment has already been submitted.'], 422);
        }

        $assessment->update(['submitted_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Assessment submitted successfully.']);
    }

    public function saveVerification(Request $request, int $id): JsonResponse
    {
        $userId     = auth('admin')->id();
        $assessment = Assessment::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 1)
            ->findOrFail($id);

        if ($assessment->submitted_at) {
            return response()->json(['success' => false, 'message' => 'This assessment has been submitted and is read-only.'], 403);
        }

        // Load ALL items in strict sequential order
        $allAcs = AssessmentChecklist::where('assessment_id', $assessment->id)
            ->with(['checklistItem.category'])
            ->get()
            ->filter(fn ($ac) => $ac->checklistItem !== null)
            ->sortBy(fn ($ac) => sprintf('%05d_%05d',
                $ac->checklistItem->category->sort_order ?? 0,
                $ac->checklistItem->sort_order ?? 0
            ))
            ->values();

        $requestItems = $request->input('items', []);

        $desiredState = $allAcs->mapWithKeys(fn ($ac) => [
            $ac->id => !empty($requestItems[$ac->id]['is_checked'] ?? '') ? 1 : 0,
        ]);

        // Enforce sequential order
        $prevChecked = true;
        foreach ($allAcs as $ac) {
            if ($desiredState[$ac->id] && !$prevChecked) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please complete checklist items in order. Each item must be completed before the next.',
                ]);
            }
            $prevChecked = (bool) $desiredState[$ac->id];
        }

        $now = now();

        DB::transaction(function () use ($allAcs, $desiredState, $userId, $now, $assessment) {
            foreach ($allAcs as $ac) {
                $isChecked = $desiredState[$ac->id];
                $ac->update([
                    'is_checked' => $isChecked,
                    'checked_by' => $isChecked ? $userId : null,
                    'checked_at' => $isChecked ? $now : null,
                ]);
            }
            // Save = final submission: mark as submitted
            $assessment->update(['submitted_at' => $now]);
        });

        $total     = $allAcs->count();
        $completed = $allAcs->filter(fn ($ac) => $desiredState[$ac->id])->count();
        $pending   = $total - $completed;
        $percent   = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        // Notify admin when all items verified
        if ($pending === 0 && $total > 0) {
            $verifier = auth('admin')->user();
            $admin    = User::where('role', 1)->first();

            if ($admin) {
                $this->sendDynamicEmail(
                    $admin->email,
                    'all_items_verified',
                    [
                        'admin_name'    => $admin->first_name,
                        'verifier_name' => trim($verifier->first_name . ' ' . $verifier->last_name),
                        'project_name'  => $assessment->name . ' (' . ($assessment->project->project_name ?? '') . ')',
                        'verified_at'   => now()->format('d M Y h:i A'),
                        'project_url'   => route('admin.project.index'),
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
    // Report
    // ------------------------------------------------------------------

    public function report(int $id): View
    {
        $userId     = auth('admin')->id();
        $assessment = Assessment::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 1)
            ->with('project')
            ->findOrFail($id);

        $reportData = $this->reportService->getReportData($assessment);
        $backUrl    = route('user.reports.index');

        return view('user.user-assessments.report', compact('reportData', 'backUrl'));
    }
}
