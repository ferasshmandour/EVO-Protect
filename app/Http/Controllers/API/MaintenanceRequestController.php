<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
use App\Http\Services\SecurityLayer;
use App\Models\EvoSystem;
use App\Models\MaintenanceRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaintenanceRequestController extends Controller
{
    private SecurityLayer $securityLayer;
    private LoggingService $loggingService;

    public function __construct(SecurityLayer $securityLayer, LoggingService $loggingService)
    {
        $this->securityLayer = $securityLayer;
        $this->loggingService = $loggingService;
    }

    public function getAllMaintenanceRequests(Request $request): JsonResponse
    {
        $role = $this->securityLayer->getRoleFromToken();
        if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
            $maintenanceRequests = DB::table('maintenance_requests', 'r')
                ->join('users as u', 'u.id', '=', 'r.user_id')
                ->join('facilities as f', 'f.id', '=', 'r.facility_id')
                ->select('r.id as maintenanceRequestId', 'u.name as username', 'u.phone', 'r.facility_id as facilityId', 'f.name as facilityName', 'r.systems', 'r.cause_of_maintenance as causeOfMaintenance', 'r.created_at as creationTime')
                ->where('r.is_deleted', '=', false)
                ->get();

            $this->loggingService->addLog($request, null);
            return response()->json($maintenanceRequests, 200);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }

    public function sentMaintenanceRequest(Request $request): JsonResponse
    {
        try {
            $role = $this->securityLayer->getRoleFromToken();
            if ($role == UserRole::user->value) {
                $validatedRequest = $request->validate([
                    'facilityId' => 'required|int',
                    'systemId' => 'required|array',
                    'systemId.*' => 'required|integer',
                    'cause_of_maintenance' => 'required|string',
                ]);

                $systemIds = $validatedRequest['systemId'] ?? [];
                $evoSystemList = [];

                foreach ($systemIds as $systemId) {
                    $evoSystem = EvoSystem::find($systemId);
                    $evoSystemList[] = $evoSystem->name;
                }

                $evoSystems = implode(', ', $evoSystemList);

                $maintenanceRequest = MaintenanceRequest::create([
                    'user_id' => $this->securityLayer->getUserIdFromToken(),
                    'facility_id' => $validatedRequest['facilityId'],
                    'systems' => $evoSystems,
                    'cause_of_maintenance' => $validatedRequest['cause_of_maintenance'],
                ]);

                Log::info("Maintenance request {$maintenanceRequest->id} sent successfully by user test from auth");

                $response = 'Maintenance request added successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['message' => 'Maintenance request added successfully'], 201);
            } else {
                return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
            }
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteMaintenanceRequest(Request $request, $maintenanceRequestId): JsonResponse
    {
        $role = $this->securityLayer->getRoleFromToken();

        if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
            $maintenanceRequest = MaintenanceRequest::findOrFail($maintenanceRequestId);

            if ($maintenanceRequest->is_deleted) {
                return response()->json(['message' => 'The maintenance request is already deleted'], 400);
            }

            $maintenanceRequest->update([
                'is_deleted' => true,
            ]);

            Log::info("Maintenance request {$maintenanceRequest->id} deleted successfully");

            $response = 'Maintenance request deleted successfully';
            $this->loggingService->addLog($request, $response);
            return response()->json(['message' => $response], 200);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }
}
