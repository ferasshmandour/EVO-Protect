<?php

namespace App\Enums;

enum FacilitySystemStatus: string
{
    case on = "ON";
    case off = "OFF";

    public static function getFacilitySystemStatus(): array
    {
        return [
            self::on, self::off
        ];
    }
}
