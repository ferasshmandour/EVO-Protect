<?php

namespace App\Http\DTO;

class FacilityValueResponse implements \JsonSerializable
{
    private ?int $facilityId;
    private ?string $facilityName;
    private ?int $systemId;
    private ?string $systemName;
    private ?string $systemStatus;
    private ?array $values;

    public function __construct($facilityId, $facilityName, $systemId, $systemName, $systemStatus, $values)
    {
        $this->facilityId = $facilityId;
        $this->facilityName = $facilityName;
        $this->systemId = $systemId;
        $this->systemName = $systemName;
        $this->systemStatus = $systemStatus;
        $this->values = $values;
    }

    public function jsonSerialize(): array
    {
        return [
            'facilityId' => $this->facilityId,
            'facilityName' => $this->facilityName,
            'systemId' => $this->systemId,
            'systemName' => $this->systemName,
            'systemStatus' => $this->systemStatus,
            'values' => $this->values
        ];
    }
}
