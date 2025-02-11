<?php

namespace App\Http\DTO;

class SystemResponse implements \JsonSerializable
{
    private ?int $systemId;
    private ?string $systemName;
    private ?string $systemDescription;
    private ?string $systemDevices;
    private ?int $facilityId;

    public function __construct($systemId, $systemName, $systemDescription, $systemDevices, $facilityId)
    {
        $this->systemId = $systemId;
        $this->systemName = $systemName;
        $this->systemDescription = $systemDescription;
        $this->systemDevices = $systemDevices;
        $this->facilityId = $facilityId;
    }

    public function jsonSerialize(): array
    {
        return [
            'systemId' => $this->systemId,
            'systemName' => $this->systemName,
            'systemDescription' => $this->systemDescription,
            'systemDevices' => $this->systemDevices,
            'facilityId' => $this->facilityId
        ];
    }
}
