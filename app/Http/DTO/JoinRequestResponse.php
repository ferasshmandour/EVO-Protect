<?php

namespace App\Http\DTO;

class JoinRequestResponse implements \JsonSerializable
{
    private ?int $id;
    private ?string $status;
    private ?int $userId;
    private ?string $username;
    private ?string $phone;
    private ?string $email;
    private ?string $addedBy;
    private ?int $numberOfFacilities;
    private array $facilities;

    public function __construct($id, $status, $userId, $username, $phone, $email, $addedBy, $numberOfFacilities, $facilities)
    {
        $this->id = $id;
        $this->status = $status;
        $this->userId = $userId;
        $this->username = $username;
        $this->phone = $phone;
        $this->email = $email;
        $this->addedBy = $addedBy;
        $this->numberOfFacilities = $numberOfFacilities;
        $this->facilities = $facilities;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'userId' => $this->userId,
            'username' => $this->username,
            'phone' => $this->phone,
            'email' => $this->email,
            'addedBy' => $this->addedBy,
            'numberOfFacilities' => $this->numberOfFacilities,
            'facilities' => $this->facilities
        ];
    }
}
