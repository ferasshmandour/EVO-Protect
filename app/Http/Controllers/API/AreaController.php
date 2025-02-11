<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
use App\Http\Services\SecurityLayer;
use App\Models\Area;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AreaController extends Controller
{
    private SecurityLayer $securityLayer;
    private LoggingService $loggingService;

    public function __construct(SecurityLayer $securityLayer, LoggingService $loggingService)
    {
        $this->securityLayer = $securityLayer;
        $this->loggingService = $loggingService;
    }

    public function getAllAreas(Request $request): JsonResponse
    {
        $areas = Area::all();
        $this->loggingService->addLog($request, null);
        return response()->json($areas, 200);
    }

    public function getAreaById(Request $request, $areaId): JsonResponse
    {
        $area = Area::where('id', $areaId)->first();
        $this->loggingService->addLog($request, null);
        return response()->json($area, 200);
    }

    public function addArea(Request $request): JsonResponse
    {
        try {
            $role = $this->securityLayer->getRoleFromToken();
            if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
                $validatedRequest = $request->validate([
                    'name' => 'required|unique:areas,name',
                ]);

                $area = Area::create([
                    'name' => $validatedRequest['name'],
                ]);

                Log::info("Area {$area->name} added successfully");

                $response = 'Area added successfully';
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

    public function updateArea(Request $request, $areaId): JsonResponse
    {
        try {
            $role = $this->securityLayer->getRoleFromToken();
            if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
                $validatedRequest = $request->validate([
                    'name' => 'sometimes|required|string',
                ]);

                $area = Area::findOrFail($areaId);
                $area->update([
                    'name' => $validatedRequest['name'] ?? $area->name,
                ]);

                Log::info("Area {$area->name} updated successfully");

                $response = 'Area updated successfully';
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

    public function deleteArea(Request $request, $areaId): JsonResponse
    {
        $role = $this->securityLayer->getRoleFromToken();
        if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
            $area = Area::findOrFail($areaId);
            $area->destroy($areaId);

            Log::info("Area {$area->name} deleted successfully");

            $response = 'Area deleted successfully';
            $this->loggingService->addLog($request, $response);
            return response()->json(['message' => $response], 200);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }
}
