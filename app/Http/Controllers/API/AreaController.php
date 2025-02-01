<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AreaController extends Controller
{
    public function getAllAreas(): JsonResponse
    {
        $areas = Area::all();
        return response()->json($areas, 200);
    }

    public function getAreaById($areaId): JsonResponse
    {
        $area = Area::where('id', $areaId)->first();
        return response()->json($area, 200);
    }

    public function addArea(Request $request): JsonResponse
    {
        try {
            $validatedRequest = $request->validate([
                'name' => 'required|unique:areas,name',
            ]);

            $area = Area::create([
                'name' => $validatedRequest['name'],
            ]);

            Log::info("Area {$area->name} added successfully");
            return response()->json(['message' => 'Area added successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateArea(Request $request, $areaId): JsonResponse
    {
        try {
            $validatedRequest = $request->validate([
                'name' => 'sometimes|required|string',
            ]);

            $area = Area::findOrFail($areaId);
            $area->update([
                'name' => $validatedRequest['name'] ?? $area->name,
            ]);

            Log::info("Area {$area->name} updated successfully");
            Log::info("Area updated successfully");
            return response()->json(['message' => 'Area updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteArea($areaId): JsonResponse
    {
        $area = Area::findOrFail($areaId);
        $area->destroy($areaId);

        Log::info("Area {$area->name} deleted successfully");
        return response()->json(['message' => 'Area deleted successfully'], 200);
    }
}
