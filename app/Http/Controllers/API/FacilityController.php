<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
use App\Http\Services\SecurityLayer;
use App\Models\Facility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $facilities = DB::table('facilities', 'f')
            ->join('users as u', 'u.id', '=', 'f.user_id')
            ->join('areas as a', 'a.id', '=', 'f.area_id')
            ->select('f.id', 'f.name', 'u.id as userId', 'u.name as username', 'a.id as areaId', 'a.name as area', 'f.location_url as locationUrl', 'f.created_at as createdAt')
            ->get();

        $this->loggingService->addLog($request, null);
        return response()->json($facilities, 200);
    }
}
