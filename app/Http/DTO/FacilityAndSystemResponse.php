<?php

namespace App\Http\DTO;

class FacilityAndSystemResponse implements \JsonSerializable
{
    private ?int $userId;
    private ?string $username;
    private ?int $facilityId;
    private ?string $facilityName;
    private ?array $facilitySystems;

    public function __construct($userId, $username, $facilityId, $facilityName, $facilitySystems)
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->facilityId = $facilityId;
        $this->facilityName = $facilityName;
        $this->facilitySystems = $facilitySystems;
    }

    public function jsonSerialize(): array
    {
        return [
            'userId' => $this->userId,
            'username' => $this->username,
            'facilityId' => $this->facilityId,
            'facilityName' => $this->facilityName,
            'facilitySystems' => $this->facilitySystems
        ];
    }
}
