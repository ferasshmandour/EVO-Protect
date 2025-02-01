<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
use App\Http\Services\SecurityLayer;
use App\Models\UserFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeedBackController extends Controller
{
    private SecurityLayer $securityLayer;
    private LoggingService $loggingService;

    public function __construct(SecurityLayer $securityLayer, LoggingService $loggingService)
    {
        $this->securityLayer = $securityLayer;
        $this->loggingService = $loggingService;
    }

    public function getAllUserFeedBacks(Request $request): JsonResponse
    {
        $role = $this->securityLayer->getRoleFromToken();
        if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
            $userFeedbacks = DB::table('user_feedbacks', 'f')
                ->join('users as u', 'f.user_id', '=', 'u.id')
                ->select('f.id as feedbackId', 'u.id as userId', 'u.name as username', 'u.phone', 'f.feedback', 'f.created_at as feedbackAddedDate')
                ->where('is_deleted','=', false)
                ->get();

            $this->loggingService->addLog($request, null);
            return response()->json($userFeedbacks, 200);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }

    public function sendFeedback(Request $request): JsonResponse
    {
        try {
            $role = $this->securityLayer->getRoleFromToken();
            if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
                $validatedRequest = $request->validate([
                    'feedback' => 'required|string',
                ]);

                $userFeedBack = UserFeedback::create([
                    'user_id' => $this->securityLayer->getUserIdFromToken(),
                    'feedback' => $validatedRequest['feedback'],
                ]);

                Log::info("User feedback {$userFeedBack->id} sent successfully by user {$userFeedBack->user->name}");

                $response = 'User feedback sent successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['message' => $response], 201);
            } else {
                return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
            }
        } catch (\Exception $e) {
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteFeedback(Request $request, $feedBackId): JsonResponse
    {
        $role = $this->securityLayer->getRoleFromToken();
        if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
            $feedbackId = UserFeedback::findOrFail($feedBackId);
            $feedbackId->update([
                'is_deleted' => true,
            ]);

            Log::info("Feedback {$feedbackId->id} deleted successfully");

            $response = 'Feedback deleted successfully';
            $this->loggingService->addLog($request, $response);
            return response()->json(['message' => $response], 200);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }
}
