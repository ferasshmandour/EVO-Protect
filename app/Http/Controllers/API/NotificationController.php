<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    private LoggingService $loggingService;

    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    public function getAllNotifications(Request $request): JsonResponse
    {
        $notifications = DB::table('notifications')
            ->select('id', 'message', 'created_at as sendDate')
            ->get();

        $this->loggingService->addLog($request, null);
        return response()->json($notifications, 200);
    }

    public function getNotificationById(Request $request, $notificationId): JsonResponse
    {
        $notification = DB::table('notifications')
            ->select('id', 'message', 'created_at as sendDate')
            ->where('id', $notificationId)
            ->get();
        $this->loggingService->addLog($request, null);
        return response()->json($notification, 200);
    }
}
