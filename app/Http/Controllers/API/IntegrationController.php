<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
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

    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    public function updateFireSystemValues(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $macAddress = $this->loggingService->getMacAddress();
            $facilitySystem = FacilitySystem::where('mac_address', $macAddress)->first();

            $systemValues = SystemValue::where(['facility_id' => $facilitySystem->facility->id, 'system_id' => $facilitySystem->system->id])->first();
            $systemValues->update([
                'temperature' => $request->temperature,
                'smoke' => strtoupper($request->smoke),
                'horn' => strtoupper($request->horn),
            ]);

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
            $macAddress = $this->loggingService->getMacAddress();
            $facilitySystem = FacilitySystem::where('mac_address', $macAddress)->first();

            $systemValues = SystemValue::where(['facility_id' => $facilitySystem->facility->id, 'system_id' => $facilitySystem->system->id])->first();
            $systemValues->update([
                'movement' => strtoupper($request->movement),
            ]);

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
            $macAddress = $this->loggingService->getMacAddress();
            $facilitySystem = FacilitySystem::where('mac_address', $macAddress)->first();

            $systemValues = SystemValue::where(['facility_id' => $facilitySystem->facility->id, 'system_id' => $facilitySystem->system->id])->first();
            $systemValues->update([
                'face_status' => strtoupper($request->faceStatus),
            ]);

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

//    public function sendNotification(Request $request, $message, $facilityCode)
//    {
//        try {
//            $notification =
//        } catch (\Exception $e) {
//            DB::rollBack();
//            $this->loggingService->addLog($request, $e->getMessage());
//            return response()->json(['message' => $e->getMessage()], 500);
//        }
//    }
}
