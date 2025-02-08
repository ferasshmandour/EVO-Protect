<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
use App\Http\Services\SecurityLayer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

            $users = User::where('status', UserStatus::active->value)->count();
            $clients = User::where('is_client', true)->get()->count();

//            select * from users u inner join facilities f on f.user_id = u.id
//inner join facility_systems fs on fs.facility_id = f.id
//inner join areas a on a.id = f.area_id
//inner join evo_systems s on s.id = fs.system_id
//where s.name like '%fire%';

//            select * from users u inner join facilities f on f.user_id = u.id
//inner join facility_systems fs on fs.facility_id = f.id
//inner join areas a on a.id = f.area_id
//where a.name = 'الجسر الأبيض';


            $this->loggingService->addLog($request, null);
            return response()->json(null, 200);
        } else {
            return response()->json(['message' => 'You don\'t have permission to perform this action'], 403);
        }
    }
}
