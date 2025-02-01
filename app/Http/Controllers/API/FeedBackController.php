<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeedBackController extends Controller
{
    public function getAllUserFeedBacks(): JsonResponse
    {
        $userFeedbacks = DB::table('user_feedbacks', 'f')
            ->join('users as u', 'f.user_id', '=', 'u.id')
            ->select('f.id as feedbackId', 'u.id as userId', 'u.name as username', 'u.phone', 'f.feedback', 'f.created_at as feedbackAddedDate')
            ->where('is_deleted', false)
            ->get();

        return response()->json($userFeedbacks, 200);
    }

    public function sendFeedback(Request $request): JsonResponse
    {
        try {
            $validatedRequest = $request->validate([
                'feedback' => 'required|string',
            ]);

            $userFeedBack = UserFeedback::create([
                //   'user_id' => auth()->user()->id,
                'user_id' => 1,
                'feedback' => $validatedRequest['feedback'],
            ]);

            Log::info("User feedback {$userFeedBack->id} sent successfully by user {$userFeedBack->user->name}");
            return response()->json(['message' => 'User feedback sent successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteFeedback($feedBackId): JsonResponse
    {
        $feedbackId = UserFeedback::findOrFail($feedBackId);
        $feedbackId->update([
            'is_deleted' => true,
        ]);

        Log::info("Feedback {$feedbackId->id} deleted successfully");
        return response()->json(['message' => 'Feedback deleted successfully'], 200);
    }
}
