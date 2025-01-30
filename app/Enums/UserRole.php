<?php

namespace App\Enums;

enum UserRole: string
{
    case user = "USER";
    case admin = "ADMIN";
    case superAdmin = "SUPER_ADMIN";

    public static function getUserRole(): array
    {
        return [
            self::user, self::admin, self::superAdmin
        ];
    }
}
