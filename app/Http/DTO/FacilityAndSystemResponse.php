<?php

namespace App\Http\DTO;

class FacilityAndSystemResponse implements \JsonSerializable
{
    private ?int $userId;
    private ?string $username;
    private ?int $facilityId;
    private ?string $facilityName;
    private ?string $facilityCode;
    private ?array $facilitySystems;

    public function __construct($userId, $username, $facilityId, $facilityName, $facilityCode, $facilitySystems)
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->facilityId = $facilityId;
        $this->facilityName = $facilityName;
        $this->facilityCode = $facilityCode;
        $this->facilitySystems = $facilitySystems;
    }

    public function jsonSerialize(): array
    {
        return [
            'userId' => $this->userId,
            'username' => $this->username,
            'facilityId' => $this->facilityId,
            'facilityName' => $this->facilityName,
            'facilityCode' => $this->facilityCode,
            'facilitySystems' => $this->facilitySystems
        ];
    }
}
