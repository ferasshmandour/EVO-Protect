<?php

namespace App\Http\Controllers\API;

use App\Enums\FacilitySystemStatus;
use App\Http\Controllers\Controller;
use App\Http\DTO\FacilityAndSystemResponse;
use App\Http\Services\LoggingService;
use App\Http\Services\SecurityLayer;
use App\Models\Facility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                ->select('fs.id as systemId', 's.name as systemName', 'fs.status')
                ->where('facility_id', $facility->id)
                ->get();
            $responseList[] = new FacilityAndSystemResponse($userId, $username, $facility->id, $facility->name, $facilitySystems);
        }

        $this->loggingService->addLog($request, null);
        return response()->json($responseList, 200);
    }

    public function getFacilityById(Request $request, $facilityId): JsonResponse
    {
        $facility = DB::table('facilities', 'f')
            ->join('facility_systems as fs', 'fs.facility_id', '=', 'f.id')
            ->join('evo_systems as s', 's.id', '=', 'fs.system_id')
            ->select('f.id as facilityId', 'f.name as facilityName', 'f.user_id', 'f.area_id', 'f.location_url', 's.id as systemId', 's.name as systemName')
            ->where('f.id', $facilityId)
            ->get();

        $this->loggingService->addLog($request, null);
        return response()->json($facility, 200);
    }

    public function turnOffFacility(Request $request, $facilityId): JsonResponse
    {
        DB::beginTransaction();
        try {
            $facility = Facility::where('id', $facilityId)->first();
            foreach ($facility->systems as $system) {
                $system->update([
                    'status' => FacilitySystemStatus::off
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
                    'status' => FacilitySystemStatus::on
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
}
