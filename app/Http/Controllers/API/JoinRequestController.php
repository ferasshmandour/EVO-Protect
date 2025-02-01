<?php

namespace App\Http\Controllers\API;

use App\Enums\FacilitySystemStatus;
use App\Enums\JoinRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\DTO\FacilityResponse;
use App\Http\DTO\JoinRequestResponse;
use App\Models\Facility;
use App\Models\FacilitySystem;
use App\Models\JoinRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JoinRequestController extends Controller
{
    public function getAllJoinRequests(): JsonResponse
    {
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

            $joinRequestResponse = new JoinRequestResponse($joinRequest->id, $joinRequest->status, $user->id, $user->name, $user->phone, $user->email, $numberOfFacilities, $facilities);
            $responseList[] = $joinRequestResponse;
        }

        return response()->json($responseList);
    }

    public function makeJoinRequest(Request $request): JsonResponse
    {
        try {
            $validatedRequest = $request->validate([
                'name' => 'required',
                'phone' => 'required|unique:users',
                'numberOfFacilities' => 'required|integer',
                'facilityName' => 'required|array', // Ensures it's an array
                'facilityName.*' => 'required|string', // Each item must be a string
                'systemTypeId' => 'required|array', // Validate systemTypeId as an array
                'systemTypeId.*' => 'required|array', // Each facility's systemTypeId should be an array
                'systemTypeId.*.*' => 'required|integer', // Each systemTypeId in the nested array should be an integer
                'areaId' => 'required|array',
                'areaId.*' => 'required|integer',
                'locationUrl' => 'nullable|array',
                'locationUrl.*' => 'nullable|url',
            ]);

            $user = User::create([
                'name' => $validatedRequest['name'],
                'phone' => $validatedRequest['phone'],
                'email' => $validatedRequest['name'] . '@gmail.com',
                'password' => bcrypt('123456'),
                'role_id' => 3, // replace this with real one
            ]);

            Log::info("User {$user->name} asked to join");

            // Loop through facilities and create them along with their systems
            for ($i = 0; $i < $validatedRequest['numberOfFacilities']; $i++) {
                $facilityName = $validatedRequest['facilityName'][$i] ?? null;
                $systemTypeIds = $validatedRequest['systemTypeId'][$i] ?? []; // Array of systemTypeId for this facility
                $areaId = $validatedRequest['areaId'][$i] ?? null;
                $locationUrl = $validatedRequest['locationUrl'][$i] ?? null;

                if (!$facilityName || empty($systemTypeIds) || !$areaId) {
                    continue; // Skip if required fields are missing
                }

                $facility = Facility::create([
                    'name' => $facilityName,
                    'area_id' => $areaId,
                    'location_url' => $locationUrl,
                    'user_id' => $user->id,
                ]);

                Log::info("Facility {$facility->name} added successfully");

                // Loop through systemTypeIds for this facility and create FacilitySystem entries
                foreach ($systemTypeIds as $systemTypeId) {
                    $facilitySystem = FacilitySystem::create([
                        'facility_id' => $facility->id,
                        'system_id' => $systemTypeId,
                        'status' => FacilitySystemStatus::off,
                    ]);

                    Log::info("Facility system {$facilitySystem->id} added successfully");
                }
            }

            $joinRequest = JoinRequest::create([
                'user_id' => $user->id,
                'status' => JoinRequestStatus::pending,
            ]);

            Log::info("Join {$joinRequest->id} request added successfully");

            return response()->json(['message' => 'Join request added successfully']);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function approveJoinRequest($joinRequestId): JsonResponse
    {
        try {
            $joinRequest = JoinRequest::where('id', $joinRequestId)->first();
            $joinRequest->update([
                'status' => JoinRequestStatus::approved,
            ]);

            Log::info("Approved join request {$joinRequest->id} successfully");

            return response()->json(['message' => 'Approved join request successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function cancelJoinRequest($joinRequestId): JsonResponse
    {
        try {
            $joinRequest = JoinRequest::where('id', $joinRequestId)->first();
            $joinRequest->update([
                'status' => JoinRequestStatus::canceled,
            ]);

            Log::info("Canceled join request {$joinRequest->id} successfully");

            return response()->json(['message' => 'Canceled join request successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
