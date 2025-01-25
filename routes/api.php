<?php

use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//    return $request->user();
//});


Route::get('/getAllUsers', [UserController::class, 'getAllUsers']);
Route::post('/addUser', [UserController::class, 'addUser']);
Route::post('/updateUser/{userId}', [UserController::class, 'updateUser']);
Route::delete('/deleteUser/{userId}', [UserController::class, 'deleteUser']);
