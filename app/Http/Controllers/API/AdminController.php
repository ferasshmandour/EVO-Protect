<?php

namespace App\Http\Controllers\API;

use App\Enums\AddedBy;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
use App\Http\Services\SecurityLayer;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    private SecurityLayer $securityLayer;
    private LoggingService $loggingService;

    public function __construct(SecurityLayer $securityLayer, LoggingService $loggingService)
    {
        $this->securityLayer = $securityLayer;
        $this->loggingService = $loggingService;
    }

    public function getAllAdmins(Request $request): JsonResponse
    {
        $role = $this->securityLayer->getRoleFromToken();
        if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
            $admins = DB::table('users', 'u')
                ->join('roles as r', 'r.id', '=', 'u.role_id')
                ->select(['u.id as userId', 'u.name as username', 'u.phone', 'u.email', 'r.name as role'])
                ->where('r.name', '=', UserRole::admin)
                ->get();

            $this->loggingService->addLog($request, null);
            return response()->json($admins, 200);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }

    public function getAdminById(Request $request, $adminId): JsonResponse
    {
        $admin = User::where(['id' => $adminId, 'role_id' => 2])->first();
        $this->loggingService->addLog($request, null);
        return response()->json($admin, 200);
    }

    public function addAdmin(Request $request): JsonResponse
    {
        try {
            $role = $this->securityLayer->getRoleFromToken();
            if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
                $validatedRequest = $request->validate([
                    'name' => 'required',
                    'phone' => 'required|unique:users',
                    'email' => 'nullable|email|unique:users',
                ]);

                $admin = User::create([
                    'name' => $validatedRequest['name'],
                    'phone' => $validatedRequest['phone'],
                    'email' => $validatedRequest['email'] ?? null,
                    'password' => bcrypt('123456'),
                    'role_id' => 2,
                    'status' => UserStatus::active,
                    'added_by' => AddedBy::dashboard,
                ]);

                Log::info("Admin {$admin->name} added successfully");

                $response = 'Admin added successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['message' => $response], 201);
            } else {
                return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
            }
        } catch (Exception $e) {
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateAdmin(Request $request, $adminId): JsonResponse
    {
        try {
            $role = $this->securityLayer->getRoleFromToken();
            if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
                $validatedRequest = $request->validate([
                    'name' => 'sometimes|required|string',
                    'phone' => 'sometimes|required|unique:users,phone,' . $adminId,
                    'email' => 'sometimes|nullable|email|unique:users,email,' . $adminId,
                ]);

                $admin = User::findOrFail($adminId);

                $admin->update([
                    'name' => $validatedRequest['name'] ?? $admin->name,
                    'phone' => $validatedRequest['phone'] ?? $admin->phone,
                    'email' => $validatedRequest['email'] ?? $admin->email,
                ]);

                Log::info("Admin {$admin->name} updated successfully");

                $response = 'Admin updated successfully';
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

    public function deleteAdmin(Request $request, $adminId): JsonResponse
    {
        $role = $this->securityLayer->getRoleFromToken();
        if ($role == UserRole::superAdmin->value) {
            $admin = User::findOrFail($adminId);
            $admin->destroy($adminId);

            Log::info("Admin {$admin->name} deleted successfully");

            $response = 'Admin deleted successfully';
            $this->loggingService->addLog($request, $response);
            return response()->json(['message' => $response], 200);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }
}
