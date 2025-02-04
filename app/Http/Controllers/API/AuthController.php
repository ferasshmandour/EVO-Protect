<?php

namespace App\Http\Controllers\API;

use App\Enums\AddedBy;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Services\LoggingService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private LoggingService $loggingService;

    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json(['Validation Error', $validator->errors()], 422);
            }

            $input = $request->all();
            $user = User::create([
                'name' => $input['username'],
                'phone' => null,
                'email' => $input['email'],
                'password' => bcrypt($input['password']),
                'role_id' => 3,
                'status' => UserStatus::active,
                'added_by' => AddedBy::mobile,
                'is_client' => false,
            ]);
            $token = $user->createToken('MyApp')->plainTextToken;

            Log::info("User {$user->name} registered successfully");

            $response = 'User registered successfully';
            $this->loggingService->addLog($request, $response);
            return response()->json(['token' => $token, 'message' => $response], 201);
        } catch (\Exception $e) {
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            if (Auth::attempt(['name' => $request->username, 'password' => $request->password])) {
                $user = Auth::user();
                $token = $user->createToken('MyApp')->plainTextToken;

                Log::info("User {$user->name} logged in successfully");

                $response = 'User logged in successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['token' => $token, 'message' => $response], 200);
            } else {
                return response()->json(['error' => 'Invalid credentials'], 422);
            }
        } catch (\Exception $e) {
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $user = Auth::user();
            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
                Log::info("User {$user->name} logged out successfully");

                $response = 'User logged out successfully';
                $this->loggingService->addLog($request, $response);
                return response()->json(['message' => $response], 200);
            }

            return response()->json(['error' => 'No active token found'], 401);
        } catch (\Exception $e) {
            $this->loggingService->addLog($request, $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
