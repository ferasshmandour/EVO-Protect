<?php

use App\Http\Controllers\API\AreaController;
use App\Http\Controllers\API\EvoSystemController;
use App\Http\Controllers\API\FeedBackController;
use App\Http\Controllers\API\JoinRequestController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// User APIs
Route::get('/getAllUsers', [UserController::class, 'getAllUsers']);
Route::get('/getUserById/{userId}', [UserController::class, 'getUserById']);
Route::post('/addUser', [UserController::class, 'addUser']);
Route::post('/updateUser/{userId}', [UserController::class, 'updateUser']);
Route::delete('/deleteUser/{userId}', [UserController::class, 'deleteUser']);
Route::get('/search', [UserController::class, 'search']);

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

// FeedBack APIs
Route::get('/getAllUserFeedBacks', [FeedBackController::class, 'getAllUserFeedBacks']);
Route::post('/sendFeedback', [FeedBackController::class, 'sendFeedback']);
Route::post('/deleteFeedback/{feedbackId}', [FeedBackController::class, 'deleteFeedback']);
