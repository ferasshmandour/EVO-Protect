<?php

namespace App\Enums;

enum JoinRequestStatus: string
{
    case pending = "PENDING";
    case canceled = "CANCELED";
    case approved = "APPROVED";

    public static function getJoinRequestStatus(): array
    {
        return [
            self::pending, self::canceled, self::approved
        ];
    }
}
