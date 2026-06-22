<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistCategory;
use App\Models\ChecklistItem;
use App\Models\Project;
use App\Models\ProjectChecklist;
use App\Models\SharedReport;
use App\Models\TechStack;
use App\Models\User;
use App\Services\ProjectReportService;
use Illuminate\Http\JsonResponse;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminProjectController extends Controller
{
    use  Common_trait;

    public function __construct(protected ProjectReportService $reportService) {}

    // ------------------------------------------------------------------
    // Listing
    // ------------------------------------------------------------------

    public function index(): View
    {
        return view('admin.projects.index');
    }

    public function projectUsers(int $id): JsonResponse
    {
        $project = Project::with(['users' => fn ($q) => $q->select('users.id', 'users.first_name', 'users.last_name', 'users.email')])->findOrFail($id);

        $users = $project->users->map(fn ($u) => [
            'id'    => $u->id,
            'name'  => $u->first_name . ' ' . $u->last_name,
            'email' => $u->email,
        ])->values();

        return response()->json(['users' => $users]);
    }

    public function projectList(Request $request): JsonResponse
    {
        $query = Project::withCount('assessments');

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

        $perPage  = 5;
        $page     = max(1, (int) $request->get('page', 1));
        $total    = $query->count();
        $lastPage = (int) ceil($total / $perPage) ?: 1;
        $page     = min($page, $lastPage);

        $projects = $query->orderBy('project_name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $html = view('admin.projects._accordion', compact('projects'))->render();

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
        $query = Project::query();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('project_name', 'like', "%{$keyword}%")
                  ->orWhere('client_name', 'like', "%{$keyword}%");
            });
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $recordsTotal    = Project::count();
        $recordsFiltered = $query->count();

        $start  = (int) ($request->start  ?? 0);
        $length = (int) ($request->length ?? 10);

        $projects = $query
            ->with('sharedReports:id,project_id,unique_token')
            ->orderBy('id', 'desc')
            ->skip($start)->take($length)
            ->get();

        $data = [];
        foreach ($projects as $index => $project) {
            $encId    = encrypt_id($project->id);
            $shareUrl = $project->sharedReports->first()?->unique_token
                ? route('project.public.report', $project->sharedReports->first()->unique_token)
                : '';

            $actions = table_action_icons(
                table_action_edit(enroute('admin.project.edit', $project->id))
                . '<a href="' . enroute('admin.project.checklist', $project->id) . '" class="table-action-btn btn-checklist" title="Assign Checklist"><i class="fa fa-list-check" aria-hidden="true"></i></a>'
                // . '<a href="' . enroute('admin.project.verify', $project->id) . '" class="table-action-btn btn-verify" title="Verify Checklist"><i class="fa fa-circle-check" aria-hidden="true"></i></a>'                
                . '<a href="' . enroute('admin.project.report', $project->id) . '" class="table-action-btn btn-report" title="Report"><i class="fa fa-chart-bar" aria-hidden="true"></i></a>'
                . '<button type="button" class="table-action-btn btn-copy-share" title="Copy Share URL"'
                  . ' data-url="' . e($shareUrl) . '"'
                  . ' onclick="ProjectList.copyShareUrl(this)">'
                  . '<i class="fa fa-link" aria-hidden="true"></i></button>'
                . table_action_delete('ProjectList.deleteProject(\'' . e($encId) . '\', \'' . route('admin.project.destroy') . '\')')
            );

            $data[] = [
                'DT_RowIndex'  => $start + $index + 1,
                'project_name' => e($project->project_name),
                'client_name'  => e($project->client_name ?? '—'),
                'status'       => '<label class="switch">
                                    <input type="checkbox" '.($project->status ? 'checked' : '').'
                                        onchange="updateStatus(\''.e($encId).'\',\'Project\',\''.route('admin.updateStatus').'\',this)">
                                    <span class="slider-table"></span>
                                </label>',
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
    // Create
    // ------------------------------------------------------------------

    public function create(): View
    {
        $verifiers  = User::where('role', 2)->where('status', 1)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);
        $techStacks = TechStack::where('status', 1)->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.projects.add', compact('verifiers', 'techStacks'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'project_name'        => ['required', 'string', 'max:255', 'unique:' . config('tables.projects') . ',project_name'],
                'project_description' => ['nullable', 'string'],
                'client_name'         => ['nullable', 'string', 'max:255'],
                'project_url'         => ['nullable', 'url', 'max:500'],
                'tech_stack_id'       => ['required', 'exists:' . config('tables.tech_stacks') . ',id'],
                'deployment_notes'    => ['nullable', 'string'],
                'status'              => ['required', 'in:0,1'],
                'assigned_users'      => ['nullable', 'array'],
                'assigned_users.*'    => ['integer', 'exists:' . config('tables.users') . ',id'],
            ]);

            $project = DB::transaction(function () use ($validated, $request) {
                $project = Project::create(collect($validated)->except('assigned_users')->toArray());

                // Sync assigned users
                $project->users()->sync($request->input('assigned_users', []));
                foreach ($project->users as $user) {
                     $this->sendDynamicEmail(
                        $user->email,
                        'project_assigned',
                        [
                            'name'          => $user->first_name,
                            'project_name'  => $project->project_name,
                            'assigned_by'   => auth()->user()->first_name,
                            'assigned_date' => now()->format('d M Y h:i A'),
                            'project_url'   => route('user.dashboard'),
                        ]
                    );
                }

                // Auto-generate public share token
                SharedReport::create([
                    'project_id'   => $project->id,
                    'unique_token' => Str::random(40),
                ]);

                return $project;
            });

            return response()->json([
                'success'      => true,
                'message'      => __('messages.project_created'),
                'redirect_url' => route('admin.project.index'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {            
            return response()->json(['success' => false, 'message' => __('messages.something_went_wrong')], 500);
        }
    }

    // ------------------------------------------------------------------
    // Show (redirect to report)
    // ------------------------------------------------------------------

    public function show(int $id): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.project-reports.index', [
            'project_id' => encrypt_id($id),
        ]);
    }

    // ------------------------------------------------------------------
    // Edit / Update
    // ------------------------------------------------------------------

    public function edit(int $id): View
    {
        $project     = Project::with('users:id')->findOrFail($id);
        $verifiers   = User::where('role', 2)->where('status', 1)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);
        $techStacks  = TechStack::where('status', 1)->orderBy('sort_order')->orderBy('name')->get();
        $assignedIds = $project->users->pluck('id')->toArray();

        return view('admin.projects.edit', compact('project', 'verifiers', 'techStacks', 'assignedIds'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);

            $validated = $request->validate([
                'project_name'        => ['required', 'string', 'max:255', 'unique:' . config('tables.projects') . ',project_name,' . $id],
                'project_description' => ['nullable', 'string'],
                'client_name'         => ['nullable', 'string', 'max:255'],
                'project_url'         => ['nullable', 'url', 'max:500'],
                'tech_stack_id'       => ['required', 'exists:' . config('tables.tech_stacks') . ',id'],
                'deployment_notes'    => ['nullable', 'string'],
                'status'              => ['required', 'in:0,1'],
                'assigned_users'      => ['nullable', 'array'],
                'assigned_users.*'    => ['integer', 'exists:' . config('tables.users') . ',id'],
            ]);

            DB::transaction(function () use ($project, $validated, $request) {
                $project->update(collect($validated)->except('assigned_users')->toArray());
                $changes = $project->users()->sync($request->input('assigned_users', []));
                $addedUsers = User::whereIn('id', $changes['attached'])->get();                
                foreach ($addedUsers as $user) {
                    $this->sendDynamicEmail(
                        $user->email,
                        'project_assigned',
                        [
                            'name'          => $user->first_name,
                            'project_name'  => $project->project_name,
                            'assigned_by'   => auth()->user()->first_name,
                            'assigned_date' => now()->format('d M Y h:i A'),
                            'project_url'   => route('user.dashboard'),
                        ]
                    );
                }
                
            });

            return response()->json([
                'success'      => true,
                'message'      => __('messages.project_updated'),
                'redirect_url' => route('admin.project.index'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {            
            return response()->json(['success' => false, 'message' => __('messages.something_went_wrong')], 500);
        }
    }

    // ------------------------------------------------------------------
    // Delete
    // ------------------------------------------------------------------

    public function destroy(Request $request): JsonResponse
    {
        $project = Project::find($request->id);

        if (!$project) {
            return response()->json(['status' => 'error', 'message' => __('messages.project_not_found')]);
        }

        ProjectChecklist::where('project_id', $project->id)->delete();
        SharedReport::where('project_id', $project->id)->delete();
        $project->users()->detach();
        $project->delete();

        return response()->json(['status' => 'success', 'message' => __('messages.project_deleted')]);
    }

    // ------------------------------------------------------------------
    // Part 3: Checklist Assignment
    // ------------------------------------------------------------------

    public function checklist(int $id): View
    {
        $project = Project::with('techStack')->findOrFail($id);
        
        $catQuery = ChecklistCategory::with(['items' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order');

        // Always filter by tech stack — null returns empty collection (empty state shown in blade)
        $catQuery->where('tech_stack_id', $project->tech_stack_id);

        $categories  = $catQuery->get();
        $assignedIds = ProjectChecklist::where('project_id', $project->id)
            ->pluck('checklist_item_id')
            ->toArray();

        return view('admin.projects.checklist', compact('project', 'categories', 'assignedIds'));
    }

    public function saveChecklist(Request $request, int $id): JsonResponse
    {
        $project     = Project::findOrFail($id);
        $selectedIds = array_map('intval', (array) $request->input('checklist_items', []));

        // Scope removal only to items in this project's tech stack
        $catQuery = ChecklistCategory::query();
        if ($project->tech_stack_id) {
            $catQuery->where('tech_stack_id', $project->tech_stack_id);
        }
        $catIds     = $catQuery->pluck('id');
        $allItemIds = ChecklistItem::whereIn('category_id', $catIds)->pluck('id')->toArray();

        DB::transaction(function () use ($project, $selectedIds, $allItemIds) {
            $toRemove = array_diff($allItemIds, $selectedIds);
            if (!empty($toRemove)) {
                ProjectChecklist::where('project_id', $project->id)
                    ->whereIn('checklist_item_id', $toRemove)
                    ->delete();
            }
            foreach ($selectedIds as $itemId) {
                ProjectChecklist::firstOrCreate(
                    ['project_id' => $project->id, 'checklist_item_id' => $itemId],
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
    // Part 4: Checklist Verification (Admin)
    // ------------------------------------------------------------------

    public function verify(int $id): View
    {
        $project = Project::with('users:id,first_name,last_name')->findOrFail($id);

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

        return view('admin.projects.verify', compact(
            'project', 'categories', 'total', 'completed', 'pending', 'percent'
        ));
    }

    public function saveVerification(Request $request, int $id): JsonResponse
    {
        $project = Project::findOrFail($id);
        $items   = $request->input('items', []);
        $adminId = auth('admin')->id();
        $now     = now();

        DB::transaction(function () use ($project, $items, $adminId, $now, $request) {
            // Save per-item verification
            foreach ($items as $pcId => $data) {
                $pc = ProjectChecklist::where('id', $pcId)
                    ->where('project_id', $project->id)
                    ->first();
                if (!$pc) continue;

                $isChecked = !empty($data['is_checked']) ? 1 : 0;
                $pc->update([
                    'is_checked' => $isChecked,
                    'checked_by' => $isChecked ? $adminId : null,
                    'checked_at' => $isChecked ? $now : null,
                ]);
            }

            // Save project-level deployment notes
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
    // Part 6: Legacy report stub → redirect to report dashboard
    // ------------------------------------------------------------------

    public function report(int $id): View
    {        
        $project = Project::find($id); 
        
        $reportData = $this->reportService->getReportData($project);
     
        return view('admin.projects.report', compact('reportData'));        
    }

    public function reportData(int $id): JsonResponse
    {
        $project = Project::findOrFail($id);

        return response()->json($this->reportService->getReportData($project));
    }

    // ------------------------------------------------------------------
    // Share URL
    // ------------------------------------------------------------------

    public function share(Request $request): JsonResponse
    {
        $project = Project::find($request->id);

        if (!$project) {
            return response()->json(['status' => 'error', 'message' => __('messages.project_not_found')]);
        }

        $shared = SharedReport::firstOrCreate(
            ['project_id' => $project->id],
            ['unique_token' => Str::random(40)]
        );

        return response()->json([
            'status'    => 'success',
            'share_url' => route('project.public.report', $shared->unique_token),
        ]);
    }
}
