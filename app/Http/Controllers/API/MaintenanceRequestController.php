<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EvoSystem;
use App\Models\MaintenanceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaintenanceRequestController extends Controller
{
    public function getAllMaintenanceRequests(): JsonResponse
    {
        $maintenanceRequests = DB::table('maintenance_requests', 'r')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->join('facilities as f', 'f.id', '=', 'r.facility_id')
            ->select('r.id as maintenanceRequestId', 'u.name as username', 'u.phone', 'r.facility_id as facilityId', 'f.name as facilityName', 'r.systems', 'r.cause_of_maintenance as causeOfMaintenance', 'r.created_at as creationTime')
            ->where('r.is_deleted', false)
            ->get();

        return response()->json($maintenanceRequests, 200);
    }

    public function sentMaintenanceRequest(Request $request): JsonResponse
    {
        try {
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
                'user_id' => 2,
                'facility_id' => $validatedRequest['facilityId'],
                'systems' => $evoSystems,
                'cause_of_maintenance' => $validatedRequest['cause_of_maintenance'],
            ]);

            Log::info("Maintenance request {$maintenanceRequest->id} sent successfully by user test from auth");
            return response()->json(['message' => 'Maintenance request added successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteMaintenanceRequest($maintenanceRequestId): JsonResponse
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($maintenanceRequestId);
        $maintenanceRequest->update([
            'is_deleted' => true,
        ]);

        Log::info("Maintenance request {$maintenanceRequest->id} deleted successfully");
        return response()->json(['message' => 'Maintenance request deleted successfully'], 200);
    }
}
