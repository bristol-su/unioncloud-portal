<?php

namespace BristolSU\UnionCloud\Models;

use BristolSU\ControlDB\Contracts\Models\DataUser;
use BristolSU\ControlDB\Contracts\Models\User;
use BristolSU\ControlDB\Traits\DataUserTrait;
use DateTime;

class NullDataUserModel implements DataUser
{
    private $id;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'id' => $this->id(),
            'first_name' => $this->firstName(),
            'last_name' => $this->lastName(),
            'email' => $this->email(),
            'dob' => $this->dob(),
            'preferred_name' => $this->preferredName()
        ];
    }

    /**
     * @inheritDoc
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function firstName(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function lastName(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function email(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function dob(): ?DateTime
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function preferredName(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function setFirstName(?string $firstName): void
    {
    }

    /**
     * @inheritDoc
     */
    public function setLastName(?string $lastName): void
    {
    }

    /**
     * @inheritDoc
     */
    public function setEmail(?string $email): void
    {
    }

    /**
     * @inheritDoc
     */
    public function setDob(?DateTime $dob): void
    {
    }

    /**
     * @inheritDoc
     */
    public function setPreferredName(?string $name): void
    {
    }

    /**
     * @inheritDoc
     */
    public function user(): ?User
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public static function addProperty(string $key): void
    {
    }

    /**
     * @inheritDoc
     */
    public static function getAdditionalAttributes(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalAttribute(string $key)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function setAdditionalAttribute(string $key, $value)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function saveAdditionalAttribute(string $key, $value)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), 0);
    }
}