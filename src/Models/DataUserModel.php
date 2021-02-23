<?php

namespace BristolSU\UnionCloud\Models;

use BristolSU\ControlDB\AdditionalProperties\AdditionalPropertyStore;
use BristolSU\ControlDB\Contracts\Models\DataUser;
use BristolSU\ControlDB\Traits\DataUserTrait;
use Carbon\Carbon;
use DateTime;
use Twigger\UnionCloud\API\Resource\User;

class DataUserModel implements DataUser
{
    use DataUserTrait;

    private int $id;

    private ?string $firstName = null;

    private ?string $lastName = null;

    private ?string $email = null;

    private ?DateTime $dob = null;

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'id' => $this->id(),
            'first_name' => $this->firstName(),
            'last_name' => $this->lastName(),
            'preferred_name' => $this->preferredName(),
            'dob' => $this->dob(),
            'email' => $this->email()
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
        return $this->firstName;
    }

    /**
     * @inheritDoc
     */
    public function lastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @inheritDoc
     */
    public function email(): ?string
    {
        return $this->email;
    }

    /**
     * @inheritDoc
     */
    public function dob(): ?DateTime
    {
        return $this->dob;
    }

    /**
     * @inheritDoc
     */
    public function preferredName(): ?string
    {
        return sprintf('%s %s', $this->firstName, $this->lastName);
    }

    /**
     * @inheritDoc
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Add an additional property
     *
     * @param $key
     */
    public static function addProperty(string $key): void
    {
        app(AdditionalPropertyStore::class)->addProperty(static::class, $key);
    }

    /**
     * Get all additional attributes the model is using
     *
     * @return array
     */
    public static function getAdditionalAttributes(): array
    {
        return (app(AdditionalPropertyStore::class)->getProperties(static::class) ?? []);
    }

    /**
     * Retrieve an additional attribute value
     *
     * @param string $key Key of the attribute
     * @return mixed Value of the attribute
     */
    public function getAdditionalAttribute(string $key)
    {
        if(property_exists($this, $key)) {
            return $this->{$key};
        }
        return null;
    }

    /**
     * Set an additional attribute value
     *
     * @param string $key Key of the attribute
     * @param mixed $value Value of the attribute
     * @throws \Exception
     */
    public function setAdditionalAttribute(string $key, $value)
    {
        throw new \Exception('Cannot update additional attributes on a unioncloud user');
    }

    /**
     * Save an additional attribute value
     *
     * @param string $key Key of the attribute
     * @param mixed $value Value of the attribute
     * @throws \Exception
     */
    public function saveAdditionalAttribute(string $key, $value)
    {
        throw new \Exception('Cannot update additional attributes on a unioncloud user');
    }

    public static function fromUnionCloudUser(User $unionCloudUser)
    {
        $user = new static;
        $user->id = $unionCloudUser->uid;
        $user->firstName = $unionCloudUser->forename;
        $user->lastName = $unionCloudUser->surname;
        if($unionCloudUser->dob !== false) { // Handles an issue from the base package where dob is false
            $user->dob = $unionCloudUser->dob;
        }
        return $user;
    }

    public function __toString()
    {
        return $this->toJson();
    }
}
