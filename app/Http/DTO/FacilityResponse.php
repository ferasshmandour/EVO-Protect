<?php

namespace App\Http\DTO;

class FacilityResponse implements \JsonSerializable
{
    private ?int $facilityId;
    private ?string $facilityName;
    private ?int $userId;
    private ?string $username;
    private ?int $areaId;
    private ?string $area;
    private ?string $facilityCode;
    private ?string $locationUrl;
    private ?int $numberOfSystems;
    private ?array $systems;

    public function __construct($facilityId, $facilityName, $userId, $username, $areaId, $area, $facilityCode, $locationUrl, $numberOfSystems, $systems)
    {
        $this->facilityId = $facilityId;
        $this->facilityName = $facilityName;
        $this->userId = $userId;
        $this->username = $username;
        $this->areaId = $areaId;
        $this->area = $area;
        $this->facilityCode = $facilityCode;
        $this->locationUrl = $locationUrl;
        $this->numberOfSystems = $numberOfSystems;
        $this->systems = $systems;
    }

    public function jsonSerialize(): array
    {
        return [
            'facilityId' => $this->facilityId,
            'facilityName' => $this->facilityName,
            'userId' => $this->userId,
            'username' => $this->username,
            'areaId' => $this->areaId,
            'area' => $this->area,
            'facilityCode' => $this->facilityCode,
            'locationUrl' => $this->locationUrl,
            'numberOfSystems' => $this->numberOfSystems,
            'systems' => $this->systems
        ];
    }
}
