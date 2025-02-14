<?php

namespace App\Http\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;

class NotificationService
{
    public function sendFireNotification($message, $macAddress, $temperature, $smoke): void
    {
        DB::beginTransaction();
        try {
            $notification = Notification::create([
                'message' => $message,
                'mac_address' => $macAddress,
                'temperature' => $temperature,
                'smoke' => strtoupper($smoke),
            ]);

            DB::commit();
            Log::info("Notification {$notification->id} sent successfully");
        } catch (Exception $e) {
            DB::rollBack();
            Log::info("Error sending notification: " . $e->getMessage());
        }
    }

    public function sendEnergyNotification($message, $macAddress, $movement): void
    {
        DB::beginTransaction();
        try {
            $notification = Notification::create([
                'message' => $message,
                'mac_address' => $macAddress,
                'movement' => strtoupper($movement),
            ]);

            DB::commit();
            Log::info("Notification {$notification->id} sent successfully");
        } catch (Exception $e) {
            DB::rollBack();
            Log::info("Error sending notification: " . $e->getMessage());
        }
    }

    public function sendProtectionNotification($message, $macAddress, $faceStatus): void
    {
        DB::beginTransaction();
        try {
            $notification = Notification::create([
                'message' => $message,
                'mac_address' => $macAddress,
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
