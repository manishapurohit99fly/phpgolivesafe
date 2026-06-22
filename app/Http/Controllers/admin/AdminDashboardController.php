<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentChecklist;
use App\Models\Project;
use App\Models\User;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminDashboardController extends Controller
{
    use Common_trait;

    public function index()
    {
        $data = $this->buildDashboardData(1);

        return view('admin.dashboard.index', $data);
    }

    public function dashboardData(Request $request)
    {
        $status = (int) $request->get('status', 1);
        $data   = $this->buildDashboardData($status);

        return response()->json([
            'success'          => true,
            'userCount'        => $data['userCount'],
            'totalProjects'    => $data['totalProjects'],
            'verifiedProjects' => $data['verifiedProjects'],
            'pendingProjects'  => $data['pendingProjects'],
            'totalItems'       => $data['totalItems'],
            'completedItems'       => $data['completedItems'],
            'pendingItems'         => $data['pendingItems'],
            'overallPercent'       => $data['overallPercent'],
            'totalAssessments'     => $data['totalAssessments'],
            'completedAssessments' => $data['completedAssessments'],
            'pendingAssessments'   => $data['pendingAssessments'],
            'projectProgress' => $data['projectProgress']->map(fn ($r) => [
                'name'      => $r['project']?->project_name ?? '—',
                'completed' => $r['completed'],
                'total'     => $r['total'],
                'percent'   => $r['percent'],
            ]),
            'recentAssessmentActivity' => $data['recentAssessmentActivity']->map(fn ($a) => [
                'assessment'  => $a->assessment?->name ?? '—',
                'project'     => $a->assessment?->project?->project_name ?? '—',
                'item'        => $a->checklistItem?->checklist_item ?? '—',
                'verified_by' => $a->checkedBy
                    ? $a->checkedBy->first_name . ' ' . $a->checkedBy->last_name
                    : '—',
                'date'        => $a->checked_at ? $a->checked_at->format('d M Y') : '—',
            ]),
            'assessmentProgress' => $data['assessmentProgress']->map(fn ($r) => [
                'name'      => $r['assessment']->name,
                'project'   => $r['project']?->project_name ?? '—',
                'completed' => $r['completed'],
                'total'     => $r['total'],
                'percent'   => $r['percent'],
            ]),
        ]);
    }

    private function buildDashboardData(int $status): array
    {
        $userCount = User::where('role', 2)->count();

        $projects   = Project::where('status', $status)->get();
        $projectIds = $projects->pluck('id');
        $totalProjects = $projects->count();

        // Single query: all assessments for these projects with AssessmentChecklist counts
        $assessmentsData = Assessment::whereIn('project_id', $projectIds)
            ->with('project:id,project_name')
            ->withCount([
                'checklists',
                'checklists as completed_count' => fn ($q) => $q->where('is_checked', 1),
            ])
            ->get();

        // Assessment stat cards
        $totalAssessments     = $assessmentsData->count();
        $completedAssessments = $assessmentsData->filter(fn ($a) => !is_null($a->submitted_at))->count();
        $pendingAssessments   = $totalAssessments - $completedAssessments;

        // Overall checklist items (from AssessmentChecklist)
        $totalItems     = $assessmentsData->sum('checklists_count');
        $completedItems = $assessmentsData->sum('completed_count');
        $pendingItems   = $totalItems - $completedItems;
        $overallPercent = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

        // Project-wise progress: group assessment checklist counts by project
        $projectProgress = $assessmentsData
            ->groupBy('project_id')
            ->map(function ($group) {
                $project   = $group->first()->project;
                $total     = $group->sum('checklists_count');
                $completed = $group->sum('completed_count');
                return [
                    'project'   => $project,
                    'total'     => $total,
                    'completed' => $completed,
                    'percent'   => $total > 0 ? round(($completed / $total) * 100) : 0,
                ];
            })
            ->sortByDesc('percent')
            ->values();

        // Verified = projects where all assessment items are completed
        $verifiedProjects = $projectProgress->filter(fn ($r) => $r['total'] > 0 && $r['percent'] === 100)->count();
        $pendingProjects  = $totalProjects - $verifiedProjects;

        // Assessment-wise progress (top 10 by percent)
        $assessmentProgress = $assessmentsData
            ->map(fn ($a) => [
                'assessment' => $a,
                'project'    => $a->project,
                'total'      => $a->checklists_count,
                'completed'  => $a->completed_count,
                'percent'    => $a->checklists_count > 0
                    ? round(($a->completed_count / $a->checklists_count) * 100)
                    : 0,
            ])
            ->sortByDesc('percent')
            ->take(10)
            ->values();

        // Recent assessment checklist activity (last 5 checked items)
        $assessmentIds = $assessmentsData->pluck('id');
        $recentAssessmentActivity = AssessmentChecklist::whereIn('assessment_id', $assessmentIds)
            ->where('is_checked', 1)
            ->with([
                'assessment:id,name,project_id',
                'assessment.project:id,project_name',
                'checklistItem:id,checklist_item',
                'checkedBy:id,first_name,last_name',
            ])
            ->orderByDesc('checked_at')
            ->limit(5)
            ->get();

        return compact(
            'userCount',
            'totalProjects', 'verifiedProjects', 'pendingProjects',
            'totalItems', 'completedItems', 'pendingItems', 'overallPercent',
            'totalAssessments', 'completedAssessments', 'pendingAssessments',
            'projectProgress', 'recentAssessmentActivity', 'assessmentProgress'
        );
    }

    public function updateStatus(Request $request)
    {
        $input = $request->all();

        $id     = $input['id'];
        $model  = $input['model'];
        $status = $input['status'];
        try {
            $modelClass = "App\\Models\\" . $model;

            if (!class_exists($modelClass)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid data'], 400);
            };
            $data = $modelClass::find($id);

            if (!$data) {
                return response()->json(['status' => 'error', 'message' => 'Data not found'], 404);
            }

            $data->status = $status;
            if ($data->save()) {
                return response()->json(['status' => 'success', 'message' => 'Status updated successfully',]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|max:15|confirmed',
        ]);

        $user = auth()->user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->password = Hash::make($request->new_password);

        if ($user->save()) {
            return redirect()->back()->with('flash-success', 'Password updated successfully');
        } else {
            return redirect()->back()->with('Oops! Something went wrong!');
        }
    }

    public function updateProfile(Request $request)
    {

        try {
            $request->validate([
                'name' => ['required', 'string', 'max:50'],
                'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);
            
            $user = auth()->user();

            $nameParts = explode(" ", $request->name, 2);
            $user->first_name = $nameParts[0];
            $user->last_name = $nameParts[1] ?? '';
            
            if ($request->file('profile_photo') != '') {
                $img = $this->file_upload(
                    $request->file('profile_photo'),
                    config('constants.UPLOADS') . '/' . config('constants.USER_PROFILE_PHOTO')
                );

                if ($img) {
                    if ($user->profile_photo && file_exists(public_path($user->profile_photo))) {
                        unlink(public_path($user->profile_photo));
                    }
                }

                $user->profile_photo = $img;
            }

            if ($user->save()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Oops! Something went wrong!'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }
}
