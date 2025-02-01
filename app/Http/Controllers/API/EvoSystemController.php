<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
use App\Http\Services\SecurityLayer;
use App\Models\EvoSystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EvoSystemController extends Controller
{
    private SecurityLayer $securityLayer;
    private LoggingService $loggingService;

    public function __construct(SecurityLayer $securityLayer, LoggingService $loggingService)
    {
        $this->securityLayer = $securityLayer;
        $this->loggingService = $loggingService;
    }

    public function getAllEvoSystems(Request $request): JsonResponse
    {
        $systems = EvoSystem::all();
        $this->loggingService->addLog($request, null);
        return response()->json($systems, 200);
    }

    public function getEvoSystemById(Request $request, $systemId): JsonResponse
    {
        $system = EvoSystem::where('id', $systemId)->first();
        $this->loggingService->addLog($request, null);
        return response()->json($system, 200);
    }

    public function addEvoSystem(Request $request): JsonResponse
    {
        try {
            $role = $this->securityLayer->getRoleFromToken();
            if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
                $validatedRequest = $request->validate([
                    'name' => 'required|unique:evo_systems,name',
                    'devices' => 'required',
                    'description' => 'nullable',
                ]);

                $system = EvoSystem::create([
                    'name' => $validatedRequest['name'],
                    'devices' => $validatedRequest['devices'],
                    'description' => $validatedRequest['description'],
                ]);

                Log::info("EvoSystem {$system->name} added successfully");

                $response = 'EvoSystem added successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['message' => $response], 201);
            } else {
                return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
            }
        } catch (\Exception $e) {
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateEvoSystem(Request $request, $systemId): JsonResponse
    {
        try {
            $role = $this->securityLayer->getRoleFromToken();
            if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
                $validatedRequest = $request->validate([
                    'name' => 'sometimes|required|string',
                    'devices' => 'sometimes|required|string',
                    'description' => 'sometimes|nullable|string',
                ]);

                $system = EvoSystem::findOrFail($systemId);
                $system->update([
                    'name' => $validatedRequest['name'] ?? $system->name,
                    'devices' => $validatedRequest['devices'] ?? $system->devices,
                    'description' => $validatedRequest['description'] ?? $system->description,
                ]);

                Log::info("EvoSystem {$system->name} updated successfully");

                $response = 'EvoSystem updated successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['message' => $response], 200);
            } else {
                return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
            }
        } catch (\Exception $e) {
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteEvoSystem(Request $request, $systemId): JsonResponse
    {
        $role = $this->securityLayer->getRoleFromToken();
        if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {
            $evoSystem = EvoSystem::findOrFail($systemId);
            $evoSystem->destroy($systemId);

            Log::info("EvoSystem {$evoSystem->name} deleted successfully");

            $response = 'EvoSystem deleted successfully';
            $this->loggingService->addLog($request, $response);
            return response()->json(['message' => $response], 200);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }
}
