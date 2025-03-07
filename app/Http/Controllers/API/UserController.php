<?php

namespace App\Http\Controllers\API;

use App\Enums\AddedBy;
use App\Enums\FacilitySystemStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\DTO\FacilityResponse;
use App\Http\DTO\SystemResponse;
use App\Http\DTO\UserResponse;
use App\Http\Services\LoggingService;
use App\Http\Services\SecurityLayer;
use App\Models\Facility;
use App\Models\FacilitySystem;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            $users = User::where('role_id', Role::where('name', UserRole::user->value)->first()->id)->get();

            $responseList = [];
            foreach ($users as $user) {
                $userFacilities = [];
                foreach ($user->facilities as $facility) {
                    $facilitySystems = [];
                    foreach ($facility->systems as $system) {
                        $facilitySystems[] = new SystemResponse(
                            $system->system->id,
                            $system->system->name,
                            $system->system->description,
                            $system->system->devices,
                            $facility->id
                        );
                    }

                    $userFacilities[] = new FacilityResponse(
                        $facility->id,
                        $facility->name,
                        $facility->user->id,
                        $facility->user->name,
                        $facility->area->id,
                        $facility->area->name,
                        $facility->code,
                        $facility->location_url,
                        $facility->systems->count(),
                        $facilitySystems
                    );
                }

                $responseList[] = new UserResponse(
                    $user->id,
                    $user->name,
                    $user->phone,
                    $user->email,
                    $user->status,
                    $user->is_client,
                    $user->added_by,
                    $user->created_at,
                    $user->role->id,
                    $user->role->name,
                    $user->facilities->count(),
                    $userFacilities
                );
            }

            $this->loggingService->addLog($request, null);
            return response()->json($responseList, 200);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }

    public function getUserById(Request $request, $userId): JsonResponse
    {
        $user = User::where('id', $userId)->first();

        $responseList = [];
        $userFacilities = [];
        foreach ($user->facilities as $facility) {
            $facilitySystems = [];
            foreach ($facility->systems as $system) {
                $facilitySystems[] = new SystemResponse(
                    $system->system->id,
                    $system->system->name,
                    $system->system->description,
                    $system->system->devices,
                    $facility->id
                );
            }

            $userFacilities[] = new FacilityResponse(
                $facility->id,
                $facility->name,
                $facility->user->id,
                $facility->user->name,
                $facility->area->id,
                $facility->area->name,
                $facility->code,
                $facility->location_url,
                $facility->systems->count(),
                $facilitySystems
            );
        }

        $responseList[] = new UserResponse(
            $user->id,
            $user->name,
            $user->phone,
            $user->email,
            $user->status,
            $user->is_client,
            $user->added_by,
            $user->created_at,
            $user->role->id,
            $user->role->name,
            $user->facilities->count(),
            $userFacilities
        );

        $this->loggingService->addLog($request, null);
        return response()->json($responseList, 200);
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

                DB::commit();

                Log::info("Admin add user successfully");
                Log::info("User {$user->name} and facilities added successfully");

                $response = 'User and facilities added successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['message' => $response], 201);
            } else {
                return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
            }
        } catch (Exception $e) {
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
                        'code' => Str::random(5),
                    ]);

                    Log::info("Facility {$facility->name} updated/added successfully");

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

                DB::commit();

                Log::info("User {$user->name} and facilities updated successfully");

                $response = 'User and facilities updated successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['message' => $response], 200);
            } else {
                return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
            }
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getUserProfile(Request $request): JsonResponse
    {
        $userId = $this->securityLayer->getUserIdFromToken();

        $user = DB::table('users', 'u')
            ->select('u.id as userId', 'u.name', 'u.phone')
            ->where('id', $userId)
            ->first();

        $this->loggingService->addLog($request, null);
        return response()->json($user, 200);
    }

    public function updateUserProfile(Request $request): JsonResponse
    {
        try {
            $userId = $this->securityLayer->getUserIdFromToken();
            $validatedRequest = $request->validate([
                'name' => 'sometimes|required|string',
                'phone' => 'sometimes|required|unique:users,phone,' . $userId,
                'password' => 'sometimes|required|string|min:6',
            ]);

            $user = User::where('id', $userId)->first();
            $user->update([
                'name' => $validatedRequest['name'],
                'phone' => $validatedRequest['phone'],
                'password' => bcrypt($validatedRequest['password']),
            ]);

            Log::info("User {$user->name} updated his profile successfully");

            $response = 'User profile updated successfully';
            $this->loggingService->addLog($request, $response);
            return response()->json(['message' => $response], 200);
        } catch (Exception $e) {
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
