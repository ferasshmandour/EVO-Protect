<?php

namespace App\Http\Controllers\API;

use App\Enums\FacilitySystemStatus;
use App\Http\Controllers\Controller;
use App\Http\DTO\FacilityAndSystemResponse;
use App\Http\DTO\FacilityValueResponse;
use App\Http\Services\LoggingService;
use App\Http\Services\SecurityLayer;
use App\Models\Facility;
use App\Models\SystemValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FacilityController extends Controller
{
    private SecurityLayer $securityLayer;
    private LoggingService $loggingService;

    public function __construct(SecurityLayer $securityLayer, LoggingService $loggingService)
    {
        $this->securityLayer = $securityLayer;
        $this->loggingService = $loggingService;
    }

    public function getAllFacilities(Request $request): JsonResponse
    {
        $userId = $this->securityLayer->getUserIdFromToken();
        $username = $this->securityLayer->getUsernameFromToken();
        $facilities = Facility::where('user_id', $userId)->get();

        $responseList = [];
        foreach ($facilities as $facility) {
            $facilitySystems = [];
            $facilitySystems[] = DB::table('facility_systems', 'fs')
                ->join('evo_systems as s', 's.id', '=', 'fs.system_id')
                ->select('fs.id as systemId', 's.name as systemName', 'fs.status', 'fs.notification_status as notificationStatus')
                ->where('facility_id', $facility->id)
                ->get();
            $responseList[] = new FacilityAndSystemResponse($userId, $username, $facility->id, $facility->name, $facility->code, $facilitySystems);
        }

        $this->loggingService->addLog($request, null);
        return response()->json($responseList, 200);
    }

    public function getFacilityById(Request $request, $facilityId): JsonResponse
    {
        $facilities = DB::table('facilities', 'f')
            ->join('facility_systems as fs', 'fs.facility_id', '=', 'f.id')
            ->join('evo_systems as s', 's.id', '=', 'fs.system_id')
            ->select('f.id as facilityId', 'f.name as facilityName', 'f.code as facilityCode', 's.id as systemId', 's.name as systemName')
            ->where('f.id', $facilityId)
            ->get();

        $responseList = [];
        foreach ($facilities as $facility) {
            $systemValues = SystemValue::where(['facility_id' => $facility->facilityId, 'system_id' => $facility->systemId])->first();
            if (Str::contains($systemValues->system->name, 'fire', true)) {
                $values = [];
                $values[] = ['temperature' => $systemValues->temperature, 'smoke' => $systemValues->smoke, 'horn' => $systemValues->horn];
                $responseList[] = new FacilityValueResponse($facility->facilityId, $facility->facilityName, $facility->facilityCode, $facility->systemId, $facility->systemName, $systemValues->status, $values);
            }
            if (Str::contains($systemValues->system->name, 'energy', true)) {
                $values = [];
                $values[] = ['movement' => $systemValues->movement];
                $responseList[] = new FacilityValueResponse($facility->facilityId, $facility->facilityName, $facility->facilityCode, $facility->systemId, $facility->systemName, $systemValues->status, $values);
            }
            if (Str::contains($systemValues->system->name, 'protect', true)) {
                $values = [];
                $values[] = ['faceStatus' => $systemValues->face_status];
                $responseList[] = new FacilityValueResponse($facility->facilityId, $facility->facilityName, $facility->facilityCode, $facility->systemId, $facility->systemName, $systemValues->status, $values);
            }
        }

        $this->loggingService->addLog($request, null);
        return response()->json($responseList, 200);
    }

    public function turnOffFacility(Request $request, $facilityId): JsonResponse
    {
        DB::beginTransaction();
        try {
            $facility = Facility::where('id', $facilityId)->first();
            foreach ($facility->systems as $system) {
                $system->update([
                    'status' => FacilitySystemStatus::off,
                    'notification_status' => FacilitySystemStatus::off,
                ]);
            }

            DB::commit();

            Log::info("The facility {$facility->id} systems have been turned off");

            $response = "The facility systems have been turned off";
            $this->loggingService->addLog($request, $response);
            return response()->json(['message' => $response], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function turnOnFacility(Request $request, $facilityId): JsonResponse
    {
        DB::beginTransaction();
        try {
            $facility = Facility::where('id', $facilityId)->first();
            foreach ($facility->systems as $system) {
                $system->update([
                    'status' => FacilitySystemStatus::on,
                    'notification_status' => FacilitySystemStatus::on,
                ]);
            }

            DB::commit();

            Log::info("The facility {$facility->id} systems have been turned on");

            $response = "The facility systems have been turned on";
            $this->loggingService->addLog($request, $response);
            return response()->json(['message' => $response], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getFacilitySettings(Request $request, $facilityId): JsonResponse
    {
        try {
            $facility = Facility::find($facilityId);

            $facilitySystems = [];
            foreach ($facility->systems as $facilitySystem) {
                $facilitySystems[] = ['name' => $facilitySystem->system->name, 'status' => $facilitySystem->status, 'notificationStatus' => $facilitySystem->notification_status];
            }

            $userId = $this->securityLayer->getUserIdFromToken();
            $username = $this->securityLayer->getUsernameFromToken();
            $response = new FacilityAndSystemResponse($userId, $username, $facility->id, $facility->name, $facility->code, $facilitySystems);

            $this->loggingService->addLog($request, null);
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateFacilitySettings(Request $request, $facilityId): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validatedRequest = $request->validate([
                'facilityName' => 'required|string',
                'systemStatus' => 'required|array',
                'systemStatus.*' => 'required|string',
                'systemNotificationStatus' => 'required|array',
                'systemNotificationStatus.*' => 'required|string',
            ]);

            $facility = Facility::find($facilityId);
            if (!$facility) {
                return response()->json(['error' => 'Facility not found'], 404);
            }

            $facility->update([
                'name' => $validatedRequest['facilityName'],
            ]);

            Log::info("Facility {$facility->id} name has been updated to {$validatedRequest['facilityName']}");

            $systems = $facility->systems;
            foreach ($systems as $index => $system) {
                if (!isset($validatedRequest['systemStatus'][$index]) || !isset($validatedRequest['systemNotificationStatus'][$index])) {
                    continue;
                }

                $system->update([
                    'status' => strtoupper($validatedRequest['systemStatus'][$index]),
                    'notification_status' => strtoupper($validatedRequest['systemNotificationStatus'][$index]),
                ]);

                Log::info("System {$system->id} updated: status = {$validatedRequest['systemStatus'][$index]}, notificationStatus = {$validatedRequest['systemNotificationStatus'][$index]}");
            }

            DB::commit();

            Log::info("Facility {$facility->id} systems have been updated successfully");

            $response = "Facility systems have been updated successfully";
            $this->loggingService->addLog($request, $response);
            return response()->json(['message' => $response], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating facility {$facilityId}: " . $e->getMessage());
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
