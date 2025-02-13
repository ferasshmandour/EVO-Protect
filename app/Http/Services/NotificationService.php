<?php

namespace App\Http\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;

class NotificationService
{
    public function sendNotification($message, $macAddress = null, $temperature = null, $smoke = null, $movement = null, $faceStatus = null): void
    {
        DB::beginTransaction();
        try {
            $notification = Notification::create([
                'message' => $message,
                'mac_address' => $macAddress,
                'temperature' => $temperature,
                'smoke' => strtoupper($smoke),
                'movement' => strtoupper($movement),
                'face_status' => strtoupper($faceStatus),
            ]);

            DB::commit();
            Log::info("Notification {$notification->id} sent successfully");
        } catch (Exception $e) {
            DB::rollBack();
            Log::info("Error sending notification: " . $e->getMessage());
        }
    }
}
