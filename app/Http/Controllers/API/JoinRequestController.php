<?php

namespace App\Http\Controllers\API;

use App\Enums\FacilitySystemStatus;
use App\Enums\JoinRequestStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\DTO\FacilityResponse;
use App\Http\DTO\JoinRequestResponse;
use App\Http\Services\LoggingService;
use App\Http\Services\SecurityLayer;
use App\Models\Facility;
use App\Models\FacilitySystem;
use App\Models\JoinRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JoinRequestController extends Controller
{
    private SecurityLayer $securityLayer;
    private LoggingService $loggingService;

    public function __construct(SecurityLayer $securityLayer, LoggingService $loggingService)
    {
        $this->securityLayer = $securityLayer;
        $this->loggingService = $loggingService;
    }

    public function getAllJoinRequests(Request $request): JsonResponse
    {
        $role = $this->securityLayer->getRoleFromToken();
        if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
            $joinRequests = JoinRequest::where('status', JoinRequestStatus::pending)->get();

            $responseList = [];
            foreach ($joinRequests as $joinRequest) {
                $user = User::where('id', $joinRequest->user_id)->first();
                $numberOfFacilities = $user->facilities->count();

                $facilities = [];
                foreach ($user->facilities as $facility) {
                    $facilityResponse = new FacilityResponse($facility->id, $facility->name, $facility->user->id, $facility->user->name, $facility->area->id, $facility->area->name, $facility->location_url);
                    $facilities[] = $facilityResponse;
                }

                $joinRequestResponse = new JoinRequestResponse($joinRequest->id, $joinRequest->status, $user->id, $user->name, $user->phone, $user->email, $joinRequest->added_by, $numberOfFacilities, $facilities);
                $responseList[] = $joinRequestResponse;
            }

            $this->loggingService->addLog($request, null);
            return response()->json($responseList);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }

    public function makeJoinRequest(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $role = $this->securityLayer->getRoleFromToken();
            if ($role == UserRole::user->value) {
                $validatedRequest = $request->validate([
                    'name' => 'required',
                    'phone' => 'required|unique:users',
                    'numberOfFacilities' => 'required|integer',
                    'facilityName' => 'required|array',
                    'facilityName.*' => 'required|string',
                    'systemTypeId' => 'required|array',
                    'systemTypeId.*' => 'required|array',
                    'systemTypeId.*.*' => 'required|integer',
                    'areaId' => 'required|array',
                    'areaId.*' => 'required|integer',
                    'locationUrl' => 'nullable|array',
                    'locationUrl.*' => 'nullable|url',
                ]);

                $userId = $this->securityLayer->getUserIdFromToken();
                $user = User::where('id', $userId)->first();
                $user->update([
                    'phone' => $validatedRequest['phone'] ?? null,
                    'is_client' => true,
                ]);

                for ($i = 0; $i < $validatedRequest['numberOfFacilities']; $i++) {
                    $facilityName = $validatedRequest['facilityName'][$i] ?? null;
                    $systemTypeIds = $validatedRequest['systemTypeId'][$i] ?? [];
                    $areaId = $validatedRequest['areaId'][$i] ?? null;
                    $locationUrl = $validatedRequest['locationUrl'][$i] ?? null;

                    if (!$facilityName || empty($systemTypeIds) || !$areaId) {
                        continue;
                    }

                    $facility = Facility::create([
                        'name' => $facilityName,
                        'area_id' => $areaId,
                        'location_url' => $locationUrl,
                        'user_id' => $user->id,
                        'code' => Str::random(5),
                    ]);

                    Log::info("Facility {$facility->name} added successfully");

                    foreach ($systemTypeIds as $systemTypeId) {
                        $facilitySystem = FacilitySystem::create([
                            'facility_id' => $facility->id,
                            'system_id' => $systemTypeId,
                            'status' => FacilitySystemStatus::off,
                            'notification_status' => FacilitySystemStatus::off,
                        ]);

                        Log::info("Facility system {$facilitySystem->id} added successfully");
                    }
                }

                $joinRequest = JoinRequest::create([
                    'user_id' => $user->id,
                    'status' => JoinRequestStatus::pending,
                ]);

                DB::commit();

                Log::info("User {$user->name} asked to join");
                Log::info("Join {$joinRequest->id} request added successfully");

                $response = 'Join request added successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['message' => $response]);
            } else {
                return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function approveJoinRequest(Request $request, $joinRequestId): JsonResponse
    {
        try {
            $role = $this->securityLayer->getRoleFromToken();
            if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
                $joinRequest = JoinRequest::where('id', $joinRequestId)->first();

                if ($joinRequest->status == JoinRequestStatus::approved->value) {
                    return response()->json(['message' => 'The join request is already approved'], 400);
                }

                $joinRequest->update([
                    'status' => JoinRequestStatus::approved,
                ]);

                Log::info("Approved join request {$joinRequest->id} successfully");

                $response = 'Join request approved successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['message' => $response]);
            } else {
                return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
            }
        } catch (\Exception $e) {
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function cancelJoinRequest(Request $request, $joinRequestId): JsonResponse
    {
        try {
            $role = $this->securityLayer->getRoleFromToken();
            if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
                $joinRequest = JoinRequest::where('id', $joinRequestId)->first();

                if ($joinRequest->status == JoinRequestStatus::canceled->value) {
                    return response()->json(['message' => 'The join request is already canceled'], 400);
                }

                $joinRequest->update([
                    'status' => JoinRequestStatus::canceled,
                ]);

                Log::info("Canceled join request {$joinRequest->id} successfully");

                $response = 'Join request canceled successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['message' => $response]);
            } else {
                return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
