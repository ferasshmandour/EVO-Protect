<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
use App\Http\Services\NotificationService;
use App\Models\EvoSystem;
use App\Models\FacilitySystem;
use App\Models\SystemValue;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IntegrationController extends Controller
{
    private LoggingService $loggingService;
    private NotificationService $notificationService;

    public function __construct(LoggingService $loggingService, NotificationService $notificationService)
    {
        $this->loggingService = $loggingService;
        $this->notificationService = $notificationService;
    }

    public function updateFireSystemValues(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $macAddress = $this->loggingService->getMacAddress($request);
            $systemId = EvoSystem::where('name', 'Fire system')->first()->id;
            $facilitySystem = FacilitySystem::where(['mac_address' => $macAddress, 'system_id' => $systemId])->first();

            $systemValues = SystemValue::where(['facility_id' => $facilitySystem->facility->id, 'system_id' => $facilitySystem->system->id])->first();
            $systemValues->update([
                'temperature' => $request->temperature,
                'smoke' => strtoupper($request->smoke),
            ]);

            $this->notificationService->sendNotification($request->message, $macAddress, $request->temperature, $request->smoke);

            DB::commit();

            Log::info("The system {$facilitySystem->system->id} values updated successfully");

            $response = 'The system values updated successfully';
            $this->loggingService->addLog($request, $response);
            return response()->json(['message' => $response], 200);
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateEnergySystemValues(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $macAddress = $this->loggingService->getMacAddress($request);
            $systemId = EvoSystem::where('name', 'Energy Saving system')->first()->id;
            $facilitySystem = FacilitySystem::where(['mac_address' => $macAddress, 'system_id' => $systemId])->first();

            $systemValues = SystemValue::where(['facility_id' => $facilitySystem->facility->id, 'system_id' => $facilitySystem->system->id])->first();
            $systemValues->update([
                'movement' => strtoupper($request->movement),
            ]);

            $this->notificationService->sendNotification($request->message, $macAddress, $request->movement);

            DB::commit();

            Log::info("The system {$facilitySystem->system->id} values updated successfully");

            $response = 'The system values updated successfully';
            $this->loggingService->addLog($request, $response);
            return response()->json(['message' => $response], 200);
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateProtectionSystemValues(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $macAddress = $this->loggingService->getMacAddress($request);
            $systemId = EvoSystem::where('name', 'Protection system')->first()->id;
            $facilitySystem = FacilitySystem::where(['mac_address' => $macAddress, 'system_id' => $systemId])->first();

            $systemValues = SystemValue::where(['facility_id' => $facilitySystem->facility->id, 'system_id' => $facilitySystem->system->id])->first();
            $systemValues->update([
                'face_status' => strtoupper($request->faceStatus),
            ]);

            $this->notificationService->sendNotification($request->message, $macAddress, $request->faceStatus);

            DB::commit();

            Log::info("The system {$facilitySystem->system->id} values updated successfully");

            $response = 'The system values updated successfully';
            $this->loggingService->addLog($request, $response);
            return response()->json(['message' => $response], 200);
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
