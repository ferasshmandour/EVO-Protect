<?php

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
