<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\SnsNotificationService;
use Illuminate\Support\Facades\Validator;
use Aws\Sns\SnsClient;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification;
use App\Http\Controllers\api\v1\BaseApiController;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
class NotificationController extends BaseApiController
{
    protected $snsService;

    public function __construct(SnsNotificationService $snsService)
    {
        $this->snsService = $snsService;
    }

    /**
     * Send push notification to a device.
     */


    public function sendPushNotification(Request $request)
    {

        // Validate request
        $request->validate([
            'message' => 'required',
            'user_id' => 'required|integer'
        ]);

        // Fetch the user by user_id
        $user = User::find($request->user_id);

        echo $request->user_id.'-- tetii --'.$user->device_id;

        // Check if user exists and has a device token
        $device_token = 'e-z_nxDCS0KK8lMKmM4y-t:APA91bFCpjSXsEFw1wzk0YgaRxBqSKQcTwplfgWxJmn6mmx7dxGo_U1a2l_PDvUlLPGNvW2WifDJw0-X9zqKTCThJIHhRzEXY49RouFGQhcl5sctUIIND2k';
        if (!$user || !$device_token) {
            return response()->json([
                'success' => false,
                'message' => __('messages.user_not_found_or_device_token_missing')
            ], 404);
        }

        // Send the push notification using the fetched device token
        $response = $this->snsService->sendPushNotification($device_token, $request->message);
        print_r($response);
        exit;

        return response()->json($response);


        return response()->json(
            $this->snsService->sendPushNotification($request->device_token, $request->message)
        );
    }

    /**
     * Send SMS notification to a phone number.
     */
    public function sendSmsNotification(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'phone_number' => 'required', // Ensure phone number is in E.164 format (+1234567890)
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }




        return response()->json(
            $this->snsService->sendSms($request->phone_number, $request->message)
        );
    }

    public function getNotifications(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', PER_PAGE); // Default to 10

            // Fetch paginated notifications
            $notifications = $user->notifications()
                ->select('id', 'type', 'data', 'read_at', 'created_at')
                ->whereNull('deleted_at')
                ->where('created_at', '>=', now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Mark unread notifications as read
            $user->notifications()
                ->whereNull('read_at')
                ->whereNull('deleted_at')
                ->update(['read_at' => now()]);



            return $this->sendResponse([
                'notifications' => $notifications->items(),
                'meta' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page'    => $notifications->lastPage(),
                    'per_page'     => $notifications->perPage(),
                    'total'        => $notifications->total(),
                ],

            ], __('messages.notifications_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->sendError(__('messages.notification_retrieval_error'), ['error' => $e->getMessage()], 500);
        }
    }
    public function deleteNotification($id)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->sendError(__('messages.user_not_found'), [], 404);
        }

        Notification::where('id', $id)
            ->where('notifiable_id', $user->id)
            ->delete(); // Soft delete directly        
        return $this->sendResponse([], __('messages.notification_deleted'));
    }
    public function deleteAllNotifications()
    {
        $user = Auth::user();
        if (!$user) {
            return $this->sendError(__('messages.user_not_found'), [], 404);
        }
        Notification::where('notifiable_id', $user->id)
            ->delete(); // Soft delete directly        
        return $this->sendResponse([], __('messages.all_notifications_cleared'));

    }
}
