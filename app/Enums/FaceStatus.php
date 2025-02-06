<?php

namespace App\Enums;

enum FaceStatus: string
{
    case knownFace = "KNOWN FACE";
    case unknownFace = "UNKNOWN FACE";
    case clearFacility = "CLEAR FACILITY";

    public static function getUserStatus(): array
    {
        return [
            self::knownFace, self::unknownFace, self::clearFacility
        ];
    }
}
