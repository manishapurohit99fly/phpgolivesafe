<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetUserPasswordRequest;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use App\Models\SiteSetting;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminUserController extends Controller
{
    use Common_trait;
    public function index()
    {
        $allInfo = User::where('role', 2)->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.manage-users.index', compact('allInfo'));
    }

    public function datatable(Request $request)
    {
        $query = User::query();
        $query->where('role', 2);
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                  ->orWhere('last_name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if ($request->start_date && $request->end_date) {
            $query->whereDate('created_at', '>=', $request->start_date)
                ->whereDate('created_at', '<=', $request->end_date);
        }
       if ($request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $recordsTotal = User::count() - 1;
        $recordsFiltered = $query->count();

        $query->orderBy('created_at', 'desc');

        $start  = $request->start ?? 0;
        $length = $request->length ?? 10;

        $users = $query->skip($start)->take($length)->get();
        
        $data = [];
        foreach ($users as $index => $user) {
            // IDs that travel back to the server (data-id, onclick, edit URL)
            // are wrapped through the centralised hasher so nothing sensitive
            // leaks into HTML or JS. The DecryptRouteIds middleware reverses
            // this on the way back in, so controllers keep using $request->id
            // and route('…', $id) without changes.
            $encId = encrypt_id($user->id);

            $actionHtml = table_action_icons(
                table_action_reset_password(
                    $encId,
                    $user->first_name . ' ' . $user->last_name,
                    $user->email ?? ''
                )
                . table_action_edit(enroute('admin.users.userEdit', $user->id))
                . table_action_delete('deleteUser(\'' . e($encId) . '\', \'' . route('admin.users.userDestroy') . '\')')
            );

            // Build the avatar cell. We render either the uploaded profile photo
            // or a deterministic initials-tile so the column always renders
            // something pleasant, even for users without a photo.
            $fullName    = trim($user->first_name . ' ' . $user->last_name);
            $initials    = strtoupper(mb_substr($user->first_name ?? '', 0, 1) . mb_substr($user->last_name ?? '', 0, 1));
            if ($initials === '') {
                $initials = strtoupper(mb_substr($user->email ?? 'U', 0, 1));
            }
            $altName = htmlspecialchars($fullName !== '' ? $fullName : ($user->email ?? 'User'), ENT_QUOTES);

            if ($user->profile_photo) {
                $imageHtml = '<span class="user-avatar-cell">'
                    . '<img src="'.asset($user->profile_photo).'" alt="'.$altName.'" class="user-avatar">'
                    . '</span>';
            } else {
                $imageHtml = '<span class="user-avatar-cell">'
                    . '<span class="user-avatar user-avatar-initials" aria-label="'.$altName.'">'.htmlspecialchars($initials, ENT_QUOTES).'</span>'
                    . '</span>';
            }

            $data[] = [
                'DT_RowIndex' => $start + $index + 1,
                'image'       => $imageHtml,
                'name'        => $user->first_name . ' ' . $user->last_name,
                'email'       => $user->email,
                'phone_no'    => $user->phone_no,
                'created_at'  => $user->created_at->format('Y-m-d'),
                'status'      => '<label class="switch">
                                    <input type="checkbox" '.($user->status ? 'checked' : '').'
                                        onchange="updateStatus(\''.e($encId).'\',\'User\',\''.route('admin.updateStatus').'\',this)">
                                    <span class="slider-table"></span>
                                </label>',
                'action'      => $actionHtml,
                ];
        }

        return response()->json([
            'draw'            => intval($request->draw),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }



    public function userAdd()
    {
        return view('admin.manage-users.add');
    }

    public function userSave(StoreAdminUserRequest $req)
    {
        if ($req->hasFile('profile_photo')) {
            $img = $this->file_upload(
                $req->file('profile_photo'),
                config('constants.UPLOADS') . '/' . config('constants.USER_PROFILE_PHOTO')
            );
        } else {
            $img = null; // or set a default image path if you have one
        }

        $user = new User();
        $user->first_name = $req->first_name;
        $user->last_name  = $req->last_name;
        $user->phone_no  = $req->phone_no;
        $user->email      = $req->email;
        $user->profile_photo      = $img;
        $user->role      = 2;
        $user->status      = $req->status;
        $user->password   = Hash::make($req->password);

        if (! $user->save()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.something_went_wrong'),
            ], 500);
        }
   
        $emailSent = $this->sendDynamicEmail(
            $user->email,
            'welcome',
            [
                'name'     => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                'email'    => $user->email,
                'password' => $req->password,
                'login_url' => '',
                'site_name' => SiteSetting::first()->site_name ?? config('app.name'),
            ]
        );
        
        return response()->json([
            'success'      => true,
            'message'      => __('messages.user_created'),
            'redirect_url' => route('admin.users.userIndex'),
        ]);
    }

    public function userEdit($id)
    {
        $singleUser = User::findOrFail($id);
        return view('admin.manage-users.edit', compact('singleUser'));
    }

    public function userUpdate(UpdateAdminUserRequest $req, $userId)
    {
        $user = User::findOrFail($userId);

        $img = $user->profile_photo;

        if ($req->file('profile_photo') != '') {
            $img = $this->file_upload(
                $req->file('profile_photo'),
                config('constants.UPLOADS') . '/' . config('constants.USER_PROFILE_PHOTO')
            );

            if ($img && $user->profile_photo && file_exists(public_path($user->profile_photo))) {
                unlink(public_path($user->profile_photo));
            }
        }

        $user->first_name = $req->first_name;
        $user->last_name  = $req->last_name;
        $user->phone_no  = $req->phone_no;
        $user->email      = $req->email;
        $user->profile_photo      = $img;
        $user->status      = $req->status;
        $user->password   = $req->password ? Hash::make($req->password) : $user->password;

        if ($user->save()) {
            return response()->json([
                'success'      => true,
                'message'      => __('messages.user_updated_successfully'),
                'redirect_url' => route('admin.users.userIndex'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.something_went_wrong'),
        ], 500);
    }

    public function userDestroy(Request $request)
    {
        $id = $request->id; 
        $user = \App\Models\User::find($id);

        if(!$user){
            return response()->json([
                'status' => 'error',
                'message' => __('messages.user_not_found')
            ]);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.user_deleted_successfully')
        ]);
    }

    /**
     * Stream a CSV export of users that exactly mirrors the filters applied
     * on the listing page (keyword, date range, status, role).
     */
    public function exportCsv(Request $request)
    {
        $query = $this->buildUserListQuery($request);

        // Build a filter-aware filename so the admin knows which slice they
        // exported (e.g. users_active_2026-05-01_to_2026-05-04_2026-05-04_153012.csv).
        $filenameParts = ['users'];

        if ($request->filled('status') && $request->status !== '') {
            $filenameParts[] = ((int) $request->status === 1) ? 'active' : 'inactive';
        }
        if ($request->filled('keyword')) {
            $filenameParts[] = 'search-' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $request->keyword);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $filenameParts[] = $request->start_date . '_to_' . $request->end_date;
        }
        $filenameParts[] = now()->format('Y_m_d_His');
        $filename = implode('_', $filenameParts) . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache',
            'Pragma'              => 'no-cache',
        ];

        $columns = [
            'S.No.',
            'First Name',
            'Last Name',
            'Email',
            'Phone No',
            'Date of Birth',
            'Status',
            'Created At',
        ];

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM so Excel renders accented characters correctly.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $columns);

            $rowNumber = 0;
            // chunkById keeps memory bounded for very large exports.
            $query->orderBy('id')->chunkById(500, function ($chunk) use ($handle, &$rowNumber) {
                foreach ($chunk as $user) {
                    $rowNumber++;
                    fputcsv($handle, [
                        $rowNumber,
                        $user->first_name,
                        $user->last_name,
                        $user->email,
                        $user->phone_no,
                        $user->dob ? \Illuminate\Support\Carbon::parse($user->dob)->format('Y-m-d') : '',
                        $user->status ? 'Active' : 'Inactive',
                        optional($user->created_at)->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Build the base query used by both the datatable and CSV export so
     * filtering logic stays in one place.
     */
    protected function buildUserListQuery(Request $request)
    {
        $query = User::query()->where('role', 2);

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                  ->orWhere('last_name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%")
                  ->orWhere('phone_no', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereDate('created_at', '>=', $request->start_date)
                  ->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        return $query;
    }

    public function checkEmail(Request $request)
    {
        $email  = $request->input('email');
        $userId = $request->input('user_id');

        $exists = User::where('email', $email)
                    ->where('id', '!=', $userId)
                    ->exists();

        return $exists ? response()->json(false) : response()->json(true);
    }

    public function checkPhone(Request $request)
    {
        $phone = $request->input('phone_no');
        $userId = $request->input('user_id');

        $exists = User::where('phone_no', $phone)
                    ->where('id', '!=', $userId) // ignore current user
                    ->exists();

        return $exists ? response()->json(false) : response()->json(true);
    }

    /**
     * Reset a user's password from the admin panel and notify the user via email.
     *
     * The password is only persisted when the notification email is sent
     * successfully. If the email fails, we roll back the change so the user
     * is never left with credentials they don't know about.
     */
    public function userResetPassword(ResetUserPasswordRequest $request)
    {
        $user = User::find($request->user_id);

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => __('messages.user_not_found'),
            ], 404);
        }

        $newPassword = $request->input('password');

        try {
            DB::transaction(function () use ($user, $newPassword) {
                $user->password = Hash::make($newPassword);

                if (! $user->save()) {
                    throw new \RuntimeException('Unable to update user password.');
                }

                $emailSent = $this->sendDynamicEmail(
                    $user->email,
                    'admin_user_password_reset',
                    [
                        'name'         => $user->first_name,
                        'email'        => $user->email,
                        'new_password' => $newPassword,
                        'login_url'    => url('/'),
                    ]
                );

                if (! $emailSent) {
                    // Rollback the password change by throwing — caught below.
                    throw new \RuntimeException(__('messages.password_reset_email_failed'));
                }
            });
        } catch (\Throwable $e) {
            Log::error('Admin reset password failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() ?: __('messages.password_reset_failed'),
            ], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => __('messages.password_reset_email_sent'),
        ]);
    }

}
