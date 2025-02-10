<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
use App\Http\Services\SecurityLayer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    private SecurityLayer $securityLayer;
    private LoggingService $loggingService;

    public function __construct(SecurityLayer $securityLayer, LoggingService $loggingService)
    {
        $this->securityLayer = $securityLayer;
        $this->loggingService = $loggingService;
    }

    public function statistics(Request $request): JsonResponse
    {
        $role = $this->securityLayer->getRoleFromToken();
        if ($role == UserRole::superAdmin->value || $role == UserRole::admin->value) {

            $users = User::all()->count();

            $usersForEachSystem = DB::table('users as u')
                ->join('facilities as f', 'f.user_id', '=', 'u.id')
                ->join('facility_systems as fs', 'fs.facility_id', '=', 'f.id')
                ->join('evo_systems as s', 's.id', '=', 'fs.system_id')
                ->select('s.id as systemId', 's.name as systemName', DB::raw('COUNT(DISTINCT u.id) as countOfUsers'))
                ->groupBy('s.id', 's.name')
                ->get();

            $mostRequiredSystems = DB::table('users as u')
                ->join('facilities as f', 'u.id', '=', 'f.user_id')
                ->join('facility_systems as fs', 'fs.facility_id', '=', 'f.id')
                ->join('evo_systems as s', 's.id', '=', 'fs.system_id')
                ->select('s.id as systemId', 's.name as systemName', DB::raw('count(s.id) as usersUsedSystem'))
                ->groupBy('s.id', 's.name')
                ->get();

            $usersByArea = DB::table('users as u')
                ->join('facilities as f', 'u.id', '=', 'f.user_id')
                ->join('areas as a', 'a.id', '=', 'f.area_id')
                ->select('a.id as areaId', 'a.name as areaName', DB::raw('count(u.id) as countOfUsers'))
                ->groupBy('a.id', 'a.name')
                ->get();

            $response = ['users' => $users, 'usersForEachSystem' => $usersForEachSystem, 'mostRequiredSystems' => $mostRequiredSystems, 'usersByArea' => $usersByArea];

            $this->loggingService->addLog($request, null);
            return response()->json($response, 200);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }
}
