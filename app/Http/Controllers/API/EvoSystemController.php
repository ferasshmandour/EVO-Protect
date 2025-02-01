<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EvoSystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EvoSystemController extends Controller
{
    public function getAllEvoSystems(): JsonResponse
    {
        $systems = EvoSystem::all();
        return response()->json($systems, 200);
    }

    public function getEvoSystemById($systemId): JsonResponse
    {
        $system = EvoSystem::where('id', $systemId)->first();
        return response()->json($system, 200);
    }

    public function addEvoSystem(Request $request): JsonResponse
    {
        try {
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

            Log::info("EvoSystem added " . $system->name);

            Log::info("EvoSystem added successfully");
            return response()->json(['message' => 'EvoSystem added successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateEvoSystem(Request $request, $systemId): JsonResponse
    {
        try {
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

            Log::info("EvoSystem updated " . $system->name);

            Log::info("EvoSystem updated successfully");
            return response()->json(['message' => 'EvoSystem updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteEvoSystem($systemId): JsonResponse
    {
        EvoSystem::destroy($systemId);
        Log::info("EvoSystem deleted successfully");
        return response()->json(['message' => 'EvoSystem deleted successfully'], 200);
    }
}
