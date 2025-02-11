<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
use App\Http\Services\SecurityLayer;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    private SecurityLayer $securityLayer;
    private LoggingService $loggingService;

    public function __construct(SecurityLayer $securityLayer, LoggingService $loggingService)
    {
        $this->securityLayer = $securityLayer;
        $this->loggingService = $loggingService;
    }

    public function getAllRoles(Request $request): JsonResponse
    {
        $role = $this->securityLayer->getRoleFromToken();
        if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
            $roles = Role::all();

            $this->loggingService->addLog($request, null);
            return response()->json($roles, 200);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }

    public function getRoleById(Request $request, $roleId): JsonResponse
    {
        $role = Role::where('id', $roleId)->first();
        $this->loggingService->addLog($request, null);
        return response()->json($role, 200);
    }

    public function changeRole(Request $request): JsonResponse
    {
        try {
            $role = $this->securityLayer->getRoleFromToken();
            if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
                $user = User::where('id', $request->userId)->firstOrFail();
                $userOldRole = $user->role->name;

                $user->update([
                    'role_id' => $request->roleId,
                ]);

                $user->refresh();
                $userNewRole = $user->role->name;

                Log::info("User {$user->name} changed role from {$userOldRole} to {$userNewRole}");

                $response = 'User changed role from successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['message' => $response], 200);
            } else {
                return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
            }
        } catch (Exception $e) {
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
