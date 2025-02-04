<?php

namespace App\Enums;

enum UserStatus: string
{
    case active = "ACTIVE";
    case inactive = "INACTIVE";

    public static function getUserStatus(): array
    {
        return [
            self::active, self::inactive
        ];
    }
}
