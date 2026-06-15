<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectChecklist;
use App\Models\User;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminDashboardController extends Controller
{
    use Common_trait;

    public function index()
    {
        $userCount = User::where('role', 2)->count();

        $projects = Project::withCount([
            'checklists',
            'checklists as completed_count' => fn ($q) => $q->where('is_checked', 1),
        ])->get();

        $totalProjects    = $projects->count();
        $verifiedProjects = $projects->filter(
            fn ($p) => $p->checklists_count > 0 && $p->checklists_count === $p->completed_count
        )->count();
        $pendingProjects  = $totalProjects - $verifiedProjects;

        $totalItems     = ProjectChecklist::count();
        $completedItems = ProjectChecklist::where('is_checked', 1)->count();
        $pendingItems   = $totalItems - $completedItems;
        $overallPercent = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

        $recentActivity = ProjectChecklist::where('is_checked', 1)
            ->with([
                'project:id,project_name',
                'checklistItem:id,checklist_item',
                'checkedBy:id,first_name,last_name',
            ])
            ->orderByDesc('checked_at')
            ->limit(5)
            ->get();

        $projectProgress = $projects->map(fn ($p) => [
            'project'   => $p,
            'total'     => $p->checklists_count,
            'completed' => $p->completed_count,
            'pending'   => $p->checklists_count - $p->completed_count,
            'percent'   => $p->checklists_count > 0
                ? round(($p->completed_count / $p->checklists_count) * 100)
                : 0,
        ])->sortByDesc('percent')->take(10)->values();

        return view('admin.dashboard.index', compact(
            'userCount',
            'totalProjects', 'verifiedProjects', 'pendingProjects',
            'totalItems', 'completedItems', 'pendingItems', 'overallPercent',
            'recentActivity', 'projectProgress'
        ));
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
