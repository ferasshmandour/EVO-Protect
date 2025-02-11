<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
use App\Models\Facility;
use App\Models\SystemValue;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            $validatedRequest = $request->validate([
                'facilityCode' => 'required',
                'temperature' => 'required',
                'smoke' => 'required',
                'horn' => 'required',
            ]);

            $facility = Facility::where('code', $validatedRequest['facilityCode'])->first();
            $systemId = 0;

            foreach ($facility->systems as $system) {
                if (Str::contains($system->system->name, 'fire', true)) {
                    $systemId = $system->system->id;
                    break;
                }
            }

            $systemValues = SystemValue::where(['facility_id' => $facility->id, 'system_id' => $systemId])->first();
            $systemValues->update([
                'temperature' => $validatedRequest['temperature'],
                'smoke' => strtoupper($validatedRequest['smoke']),
                'horn' => strtoupper($validatedRequest['horn']),
            ]);

            DB::commit();

            Log::info("The system {$systemId} values updated successfully");

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
            $validatedRequest = $request->validate([
                'facilityCode' => 'required',
                'movement' => 'required',
            ]);

            $facility = Facility::where('code', $validatedRequest['facilityCode'])->first();
            $systemId = 0;

            foreach ($facility->systems as $system) {
                if (Str::contains($system->system->name, 'energy', true)) {
                    $systemId = $system->system->id;
                    break;
                }
            }

            $systemValues = SystemValue::where(['facility_id' => $facility->id, 'system_id' => $systemId])->first();
            $systemValues->update([
                'movement' => strtoupper($validatedRequest['movement']),
            ]);

            DB::commit();

            Log::info("The system {$systemId} values updated successfully");

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
            $validatedRequest = $request->validate([
                'facilityCode' => 'required',
                'faceStatus' => 'required',
            ]);

            $facility = Facility::where('code', $validatedRequest['facilityCode'])->first();
            $systemId = 0;

            foreach ($facility->systems as $system) {
                if (Str::contains($system->system->name, 'protection', true)) {
                    $systemId = $system->system->id;
                    break;
                }
            }

            $systemValues = SystemValue::where(['facility_id' => $facility->id, 'system_id' => $systemId])->first();
            $systemValues->update([
                'face_status' => strtoupper($validatedRequest['faceStatus']),
            ]);

            DB::commit();

            Log::info("The system {$systemId} values updated successfully");

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
