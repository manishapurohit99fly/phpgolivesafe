<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentChecklist;
use App\Models\ChecklistCategory;
use App\Models\ChecklistItem;
use App\Models\Project;
use App\Models\SharedReport;
use App\Models\User;
use App\Services\AssessmentReportService;
use App\Traits\Common_trait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAssessmentController extends Controller
{
    use Common_trait;

    public function __construct(protected AssessmentReportService $reportService) {}

    // ------------------------------------------------------------------
    // List assessments for a project (AJAX, used by project accordion)
    // ------------------------------------------------------------------

    public function forProject(int $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);

        $assessments = Assessment::where('project_id', $projectId)
            ->withCount([
                'checklists',
                'checklists as completed_count' => fn ($q) => $q->where('is_checked', 1),
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $html = view('admin.assessments._list', compact('project', 'assessments'))->render();

        return response()->json(['html' => $html]);
    }

    // ------------------------------------------------------------------
    // Create assessment (from modal — AJAX POST)
    // ------------------------------------------------------------------

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'project_id'    => ['required', 'integer', 'exists:' . config('tables.projects') . ',id'],
                'name'          => ['required', 'string', 'max:255'],
                'description'   => ['nullable', 'string'],
                'assigned_user' => ['nullable', 'integer', 'exists:' . config('tables.users') . ',id'],
            ]);

            $assessment = DB::transaction(function () use ($validated) {
                $assessment = Assessment::create([
                    'project_id'  => $validated['project_id'],
                    'name'        => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'status'      => 1,
                    'created_by'  => auth('admin')->id(),
                ]);

                $userId = $validated['assigned_user'] ?? null;
                $assessment->users()->sync($userId ? [$userId] : []);

                SharedReport::create([
                    'project_id'    => $assessment->project_id,
                    'assessment_id' => $assessment->id,
                    'unique_token'  => Str::random(40),
                ]);

                return $assessment;
            });

            return response()->json([
                'success'    => true,
                'message'    => 'Assessment created successfully.',
                'project_id' => $assessment->project_id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('messages.something_went_wrong')], 500);
        }
    }

    // ------------------------------------------------------------------
    // Edit / Update assessment
    // ------------------------------------------------------------------

    public function edit(int $id): View
    {
        $assessment   = Assessment::with(['users:id', 'project.users:id,first_name,last_name,email'])->findOrFail($id);
        $projectUsers = $assessment->project?->users ?? collect();
        $assignedId   = $assessment->users->first()?->id;

        return view('admin.assessments.edit', compact('assessment', 'projectUsers', 'assignedId'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $assessment = Assessment::findOrFail($id);

            $validated = $request->validate([
                'name'          => ['required', 'string', 'max:255'],
                'description'   => ['nullable', 'string'],
                'status'        => ['required', 'in:0,1'],
                'assigned_user' => ['nullable', 'integer', 'exists:' . config('tables.users') . ',id'],
            ]);

            DB::transaction(function () use ($assessment, $validated) {
                $assessment->update([
                    'name'        => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'status'      => $validated['status'],
                ]);
                $userId = $validated['assigned_user'] ?? null;
                $assessment->users()->sync($userId ? [$userId] : []);
            });

            return response()->json([
                'success'      => true,
                'message'      => 'Assessment updated successfully.',
                'redirect_url' => route('admin.project.index'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('messages.something_went_wrong')], 500);
        }
    }

    // ------------------------------------------------------------------
    // Delete assessment
    // ------------------------------------------------------------------

    public function destroy(Request $request): JsonResponse
    {
        $assessment = Assessment::find($request->id);

        if (!$assessment) {
            return response()->json(['status' => 'error', 'message' => 'Assessment not found.']);
        }

        DB::transaction(function () use ($assessment) {
            AssessmentChecklist::where('assessment_id', $assessment->id)->delete();
            SharedReport::where('assessment_id', $assessment->id)->delete();
            $assessment->users()->detach();
            $assessment->delete();
        });

        return response()->json(['status' => 'success', 'message' => 'Assessment deleted successfully.']);
    }

    // ------------------------------------------------------------------
    // Checklist Assignment
    // ------------------------------------------------------------------

    public function checklist(int $id): View
    {
        $assessment = Assessment::with('project.techStack')->findOrFail($id);
        $project    = $assessment->project;

        // Filter categories by the parent project's tech stack
        $categories = ChecklistCategory::with(['items' => fn ($q) => $q->orderBy('sort_order')])
            ->where('tech_stack_id', $project?->tech_stack_id)
            ->orderBy('sort_order')
            ->get();

        $assignedIds = AssessmentChecklist::where('assessment_id', $assessment->id)
            ->pluck('checklist_item_id')
            ->toArray();

        return view('admin.assessments.checklist', compact('assessment', 'categories', 'assignedIds'));
    }

    public function saveChecklist(Request $request, int $id): JsonResponse
    {
        $assessment  = Assessment::with('project')->findOrFail($id);
        $selectedIds = array_map('intval', (array) $request->input('checklist_items', []));

        // Scope removal to items belonging to the project's tech stack only
        $catIds     = ChecklistCategory::where('tech_stack_id', $assessment->project?->tech_stack_id)->pluck('id');
        $allItemIds = ChecklistItem::whereIn('category_id', $catIds)->pluck('id')->toArray();

        DB::transaction(function () use ($assessment, $selectedIds, $allItemIds) {
            $toRemove = array_diff($allItemIds, $selectedIds);
            if (!empty($toRemove)) {
                AssessmentChecklist::where('assessment_id', $assessment->id)
                    ->whereIn('checklist_item_id', $toRemove)
                    ->delete();
            }
            foreach ($selectedIds as $itemId) {
                AssessmentChecklist::firstOrCreate(
                    ['assessment_id' => $assessment->id, 'checklist_item_id' => $itemId],
                    ['is_checked' => 0]
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => __('messages.checklist_assigned'),
        ]);
    }

    // ------------------------------------------------------------------
    // Verification
    // ------------------------------------------------------------------

    public function verify(int $id): View
    {
        $assessment = Assessment::with(['project', 'users:id,first_name,last_name'])->findOrFail($id);

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

        return view('admin.assessments.verify', compact(
            'assessment', 'categories', 'total', 'completed', 'pending', 'percent'
        ));
    }

    public function saveVerification(Request $request, int $id): JsonResponse
    {
        $assessment = Assessment::findOrFail($id);
        $items      = $request->input('items', []);
        $adminId    = auth('admin')->id();
        $now        = now();

        DB::transaction(function () use ($assessment, $items, $adminId, $now, $request) {
            foreach ($items as $acId => $data) {
                $ac = AssessmentChecklist::where('id', $acId)
                    ->where('assessment_id', $assessment->id)
                    ->first();
                if (!$ac) continue;

                $isChecked = !empty($data['is_checked']) ? 1 : 0;
                $ac->update([
                    'is_checked' => $isChecked,
                    'checked_by' => $isChecked ? $adminId : null,
                    'checked_at' => $isChecked ? $now : null,
                ]);
            }

            if ($request->has('deployment_notes')) {
                $assessment->update(['description' => $request->deployment_notes]);
            }
        });

        $total     = AssessmentChecklist::where('assessment_id', $assessment->id)->count();
        $completed = AssessmentChecklist::where('assessment_id', $assessment->id)->where('is_checked', 1)->count();
        $pending   = $total - $completed;
        $percent   = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

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
        $assessment = Assessment::with('project')->findOrFail($id);
        $reportData = $this->reportService->getReportData($assessment);

        return view('admin.assessments.report', compact('reportData'));
    }

    public function reportData(int $id): JsonResponse
    {
        $assessment = Assessment::findOrFail($id);

        return response()->json($this->reportService->getReportData($assessment));
    }

    // ------------------------------------------------------------------
    // Share
    // ------------------------------------------------------------------

    public function share(Request $request): JsonResponse
    {
        $assessment = Assessment::find($request->id);

        if (!$assessment) {
            return response()->json(['status' => 'error', 'message' => 'Assessment not found.']);
        }

        $shared = SharedReport::firstOrCreate(
            ['assessment_id' => $assessment->id],
            [
                'project_id'   => $assessment->project_id,
                'unique_token' => Str::random(40),
            ]
        );

        return response()->json([
            'status'    => 'success',
            'share_url' => route('project.public.report', $shared->unique_token),
        ]);
    }
}
