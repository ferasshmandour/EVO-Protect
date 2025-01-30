<?php

namespace App\Http\DTO;

class FacilityResponse implements \JsonSerializable
{
    private ?int $id;
    private ?string $name;
    private ?int $userId;
    private ?string $username;
    private ?int $areaId;
    private ?string $area;
    private ?string $locationUrl;

    public function __construct($id, $name, $userId, $username, $areaId, $area, $locationUrl)
    {
        $this->id = $id;
        $this->name = $name;
        $this->userId = $userId;
        $this->username = $username;
        $this->areaId = $areaId;
        $this->area = $area;
        $this->locationUrl = $locationUrl;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'userId' => $this->userId,
            'username' => $this->username,
            'areaId' => $this->areaId,
            'area' => $this->area,
            'locationUrl' => $this->locationUrl
        ];
    }
}
