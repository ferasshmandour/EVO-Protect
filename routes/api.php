<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AreaController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\EvoSystemController;
use App\Http\Controllers\API\FacilityController;
use App\Http\Controllers\API\FeedBackController;
use App\Http\Controllers\API\JoinRequestController;
use App\Http\Controllers\API\MaintenanceRequestController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Auth APIs
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // User APIs
    Route::get('/getAllUsers', [UserController::class, 'getAllUsers']);
    Route::get('/getUserById/{userId}', [UserController::class, 'getUserById']);
    Route::post('/addUser', [UserController::class, 'addUser']);
    Route::post('/updateUser/{userId}', [UserController::class, 'updateUser']);
    Route::delete('/deleteUser/{userId}', [UserController::class, 'deleteUser']);
    Route::get('/search', [UserController::class, 'search']);
    Route::get('/getUserProfile', [UserController::class, 'getUserProfile']);
    Route::post('/updateUserProfile', [UserController::class, 'updateUserProfile']);

    // Admin APIs
    Route::get('/getAllAdmins', [AdminController::class, 'getAllAdmins']);
    Route::get('/getAdminById/{adminId}', [AdminController::class, 'getAdminById']);
    Route::post('/addAdmin', [AdminController::class, 'addAdmin']);
    Route::post('/updateAdmin/{adminId}', [AdminController::class, 'updateAdmin']);
    Route::delete('/deleteAdmin/{adminId}', [AdminController::class, 'deleteAdmin']);

    // Role APIs
    Route::get('/getAllRoles', [RoleController::class, 'getAllRoles']);
    Route::get('/getRoleById/{roleId}', [RoleController::class, 'getRoleById']);
    Route::post('/changeRole', [RoleController::class, 'changeRole']);

    // JoinRequest APIs
    Route::post('/makeJoinRequest', [JoinRequestController::class, 'makeJoinRequest']);
    Route::get('/getAllJoinRequests', [JoinRequestController::class, 'getAllJoinRequests']);
    Route::post('/approveJoinRequest/{joinRequestId}', [JoinRequestController::class, 'approveJoinRequest']);
    Route::post('/cancelJoinRequest/{joinRequestId}', [JoinRequestController::class, 'cancelJoinRequest']);

    // Area APIs
    Route::get('/getAllAreas', [AreaController::class, 'getAllAreas']);
    Route::get('/getAreaById/{areaId}', [AreaController::class, 'getAreaById']);
    Route::post('/addArea', [AreaController::class, 'addArea']);
    Route::post('/updateArea/{areaId}', [AreaController::class, 'updateArea']);
    Route::delete('/deleteArea/{areaId}', [AreaController::class, 'deleteArea']);

    // EvoSystem APIs
    Route::get('/getAllEvoSystems', [EvoSystemController::class, 'getAllEvoSystems']);
    Route::get('/getEvoSystemById/{systemId}', [EvoSystemController::class, 'getEvoSystemById']);
    Route::post('/addEvoSystem', [EvoSystemController::class, 'addEvoSystem']);
    Route::post('/updateEvoSystem/{systemId}', [EvoSystemController::class, 'updateEvoSystem']);
    Route::delete('/deleteEvoSystem/{systemId}', [EvoSystemController::class, 'deleteEvoSystem']);

    // Feedback APIs
    Route::get('/getAllUserFeedBacks', [FeedBackController::class, 'getAllUserFeedBacks']);
    Route::post('/sendFeedback', [FeedBackController::class, 'sendFeedback']);
    Route::post('/deleteFeedback/{feedbackId}', [FeedBackController::class, 'deleteFeedback']);

    // MaintenanceRequest APIs
    Route::get('/getAllMaintenanceRequests', [MaintenanceRequestController::class, 'getAllMaintenanceRequests']);
    Route::post('/sentMaintenanceRequest', [MaintenanceRequestController::class, 'sentMaintenanceRequest']);
    Route::post('/deleteMaintenanceRequest/{maintenanceRequestId}', [MaintenanceRequestController::class, 'deleteMaintenanceRequest']);

    // Facility APIs
    Route::get('/getAllFacilities', [FacilityController::class, 'getAllFacilities']);
    Route::get('/getFacilityById/{facilityId}', [FacilityController::class, 'getFacilityById']);
    Route::post('/turnOffFacility/{facilityId}', [FacilityController::class, 'turnOffFacility']);
    Route::post('/turnOnFacility/{facilityId}', [FacilityController::class, 'turnOnFacility']);
});

Route::get('/clearCache', [UserController::class, 'clearCache']);
