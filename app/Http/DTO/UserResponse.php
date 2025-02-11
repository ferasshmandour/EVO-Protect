<?php

namespace App\Http\DTO;

class UserResponse implements \JsonSerializable
{
    private ?int $userId;
    private ?string $username;
    private ?string $phone;
    private ?string $email;
    private ?string $status;
    private ?string $isClient;
    private ?string $addedBy;
    private ?string $createdAt;
    private ?int $roleId;
    private ?string $roleName;
    private ?int $numberOfFacilities;
    private ?array $facilities;

    public function __construct($userId, $username, $phone, $email, $status, $isClient, $addedBy, $createdAt, $roleId, $roleName, $numberOfFacilities, $facilities)
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->phone = $phone;
        $this->email = $email;
        $this->status = $status;
        $this->isClient = $isClient;
        $this->addedBy = $addedBy;
        $this->createdAt = $createdAt;
        $this->roleId = $roleId;
        $this->roleName = $roleName;
        $this->numberOfFacilities = $numberOfFacilities;
        $this->facilities = $facilities;
    }

    public function jsonSerialize(): array
    {
        return [
            'userId' => $this->userId,
            'username' => $this->username,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status,
            'isClient' => $this->isClient,
            'addedBy' => $this->addedBy,
            'createdAt' => $this->createdAt,
            'roleId' => $this->roleId,
            'roleName' => $this->roleName,
            'numberOfFacilities' => $this->numberOfFacilities,
            'facilities' => $this->facilities,
        ];
    }
}
