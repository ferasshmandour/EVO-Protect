<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FacilitySystem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    public function getAllUsers(): JsonResponse
    {
        $users = User::all();
        return response()->json($users);
    }

    public function addUser(Request $request): JsonResponse
    {
        try {
            // Validate input
            $validatedRequest = $request->validate([
                'name' => 'required',
                'phone' => 'required|unique:users',
                'email' => 'nullable|email|unique:users',
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

            // Create the user
            $user = User::create([
                'name' => $validatedRequest['name'],
                'phone' => $validatedRequest['phone'],
                'email' => $validatedRequest['email'] ?? null,
                'password' => bcrypt('123456'),
                'role_id' => 1,
            ]);

            Log::info("User added " . $user->name);

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

                Log::info("Facility added " . $facility->name);

                // Loop through systemTypeIds for this facility and create FacilitySystem entries
                foreach ($systemTypeIds as $systemTypeId) {
                    $facilitySystem = FacilitySystem::create([
                        'facility_id' => $facility->id,
                        'system_id' => $systemTypeId,
                        'status' => 'any',
                    ]);

                    Log::info("Facility system added " . $facilitySystem->id);
                }
            }

            Log::info("User and facilities added successfully");
            return response()->json(['message' => 'User and facilities added successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateUser(Request $request, $userId): JsonResponse
    {
        try {
            // Validate input
            $validatedRequest = $request->validate([
                'name' => 'sometimes|required|string',
                'phone' => 'sometimes|required|unique:users,phone,' . $userId, // Ignore current user's phone
                'email' => 'sometimes|nullable|email|unique:users,email,' . $userId, // Ignore current user's email
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

            // Fetch the user
            $user = User::findOrFail($userId);

            // Update user information
            $user->update([
                'name' => $validatedRequest['name'] ?? $user->name,
                'phone' => $validatedRequest['phone'] ?? $user->phone,
                'email' => $validatedRequest['email'] ?? $user->email,
            ]);

            Log::info("User updated " . $user->name);

            // Clear existing facilities and their systems
            $user->facilities()->delete();

            // Re-add facilities and their systems
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

                Log::info("Facility updated/added " . $facility->name);

                // Loop through systemTypeIds for this facility and create FacilitySystem entries
                foreach ($systemTypeIds as $systemTypeId) {
                    $facilitySystem = FacilitySystem::create([
                        'facility_id' => $facility->id,
                        'system_id' => $systemTypeId,
                        'status' => 'any',
                    ]);

                    Log::info("Facility system added " . $facilitySystem->id);
                }
            }

            Log::info("User and facilities updated successfully");
            return response()->json(['message' => 'User and facilities updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteUser($userId): JsonResponse
    {
        User::destroy($userId);
        Log::info("User deleted successfully");
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function test(): JsonResponse
    {
        $facilitySystem = FacilitySystem::where('id', 3)->first();
        $user = $facilitySystem->facility->user->name;
        return response()->json($user);
    }
}
