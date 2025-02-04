<?php

namespace App\Http\Controllers\API;

use App\Enums\AddedBy;
use App\Enums\FacilitySystemStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
use App\Http\Services\SecurityLayer;
use App\Models\Facility;
use App\Models\FacilitySystem;
use App\Models\JoinRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    private SecurityLayer $securityLayer;
    private LoggingService $loggingService;

    public function __construct(SecurityLayer $securityLayer, LoggingService $loggingService)
    {
        $this->securityLayer = $securityLayer;
        $this->loggingService = $loggingService;
    }

    public function getAllUsers(Request $request): JsonResponse
    {
        $role = $this->securityLayer->getRoleFromToken();
        if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
            $users = DB::table('users', 'u')
                ->join('roles as r', 'r.id', '=', 'u.role_id')
                ->select([
                    'u.id as userId',
                    'u.name as username',
                    'u.phone',
                    'u.email',
                    'r.name as role',
                    'u.status',
                    DB::raw("case when u.is_client = 1 then 'YES' else 'NO' end as isClient"),
                    'u.added_by as addedBy',
                    'u.created_at as createdAt'
                ])->where('r.name', '=', UserRole::user)
                ->get();

            $this->loggingService->addLog($request, null);
            return response()->json($users, 200);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }

    public function getUserById(Request $request, $userId): JsonResponse
    {
        $user = User::where('id', $userId)->first();
        $this->loggingService->addLog($request, null);
        return response()->json($user, 200);
    }

    public function addUser(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $role = $this->securityLayer->getRoleFromToken();
            if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
                $validatedRequest = $request->validate([
                    'name' => 'required',
                    'phone' => 'required|unique:users',
                    'email' => 'nullable|email|unique:users',
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

                $user = User::create([
                    'name' => $validatedRequest['name'],
                    'phone' => $validatedRequest['phone'],
                    'email' => $validatedRequest['email'] ?? null,
                    'password' => bcrypt('123456'),
                    'role_id' => 3,
                    'status' => UserStatus::active,
                    'added_by' => AddedBy::dashboard,
                    'is_client' => true,
                ]);

                Log::info("User {$user->name} added successfully");

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
                    ]);

                    Log::info("Facility {$facility->name} added successfully");

                    foreach ($systemTypeIds as $systemTypeId) {
                        $facilitySystem = FacilitySystem::create([
                            'facility_id' => $facility->id,
                            'system_id' => $systemTypeId,
                            'status' => FacilitySystemStatus::off,
                        ]);

                        Log::info("Facility system {$facilitySystem->id} added successfully");
                    }
                }

                DB::commit();

                Log::info("Admin add user successfully");
                Log::info("User {$user->name} and facilities added successfully");

                $response = 'User and facilities added successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['message' => $response], 201);
            } else {
                return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateUser(Request $request, $userId): JsonResponse
    {
        DB::beginTransaction();
        try {
            $role = $this->securityLayer->getRoleFromToken();
            if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
                $validatedRequest = $request->validate([
                    'name' => 'sometimes|required|string',
                    'phone' => 'sometimes|required|unique:users,phone,' . $userId,
                    'email' => 'sometimes|nullable|email|unique:users,email,' . $userId,
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

                $user = User::findOrFail($userId);

                $user->update([
                    'name' => $validatedRequest['name'] ?? $user->name,
                    'phone' => $validatedRequest['phone'] ?? $user->phone,
                    'email' => $validatedRequest['email'] ?? $user->email,
                ]);

                Log::info("User {$user->name} updated successfully");

                $user->facilities()->delete();

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
                    ]);

                    Log::info("Facility {$facility->name} updated/added successfully");

                    foreach ($systemTypeIds as $systemTypeId) {
                        $facilitySystem = FacilitySystem::create([
                            'facility_id' => $facility->id,
                            'system_id' => $systemTypeId,
                            'status' => FacilitySystemStatus::off,
                        ]);

                        Log::info("Facility system {$facilitySystem->id} added successfully");
                    }
                }

                DB::commit();

                Log::info("User {$user->name} and facilities updated successfully");

                $response = 'User and facilities updated successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['message' => $response], 200);
            } else {
                return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteUser(Request $request, $userId): JsonResponse
    {
        $role = $this->securityLayer->getRoleFromToken();
        if ($role == UserRole::superAdmin->value) {
            $user = User::findOrFail($userId);
            $user->destroy($userId);

            Log::info("User {$user->name} deleted successfully");

            $response = 'User deleted successfully';
            $this->loggingService->addLog($request, $response);
            return response()->json(['message' => $response], 200);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $sql = DB::table('users')
                ->where('name', 'like', '%' . $request->username . '%')
                ->where('role_id', '=', $request->roleId)
                ->get();
            return response()->json($sql, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function clearCache(): string
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('config:cache');
        return 'cleared';
    }
}
