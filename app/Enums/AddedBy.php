<?php

namespace App\Enums;

enum AddedBy: string
{
    case mobile = "MOBILE";
    case dashboard = "DASHBOARD";

    public static function getAddedBy(): array
    {
        return [
            self::mobile, self::dashboard
        ];
    }
}
