<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FacilitySystem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
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
                'systemTypeId' => 'required|array',
                'systemTypeId.*' => 'required|integer',
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
                'role_id' => 1, // Normal user role
            ]);

            // Loop through facilities and create them along with their systems
            for ($i = 0; $i < $validatedRequest['numberOfFacilities']; $i++) {
                $facilityName = $validatedRequest['facilityName'][$i] ?? null;
                $systemTypeId = $validatedRequest['systemTypeId'][$i] ?? null;
                $areaId = $validatedRequest['areaId'][$i] ?? null;
                $locationUrl = $validatedRequest['locationUrl'][$i] ?? null;

                if (!$facilityName || !$systemTypeId || !$areaId) {
                    continue; // Skip if required fields are missing
                }

                $facility = Facility::create([
                    'name' => $facilityName,
                    'area_id' => $areaId,
                    'location_url' => $locationUrl,
                    'user_id' => $user->id,
                ]);

                FacilitySystem::create([
                    'facility_id' => $facility->id,
                    'system_id' => $systemTypeId,
                    'status' => 'any',
                ]);
            }

            return response()->json(['message' => 'User and facilities added successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
