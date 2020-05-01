<?php

namespace BristolSU\UnionCloud\Implementations;

use BristolSU\ControlDB\Contracts\Repositories\DataUser;
use BristolSU\UnionCloud\Exception\PermissionDeniedException;
use BristolSU\UnionCloud\Models\DataUserModel;
use BristolSU\UnionCloud\Models\NullDataUserModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use BristolSU\UnionCloud\UnionCloud\UnionCloudContract as UnionCloud;
use Illuminate\Support\Collection;

class DataUserRepository implements DataUser
{

    /**
     * @var UnionCloud
     */
    private $unionCloud;

    public function __construct(UnionCloud $unionCloud)
    {
        $this->unionCloud = $unionCloud;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id): \BristolSU\ControlDB\Contracts\Models\DataUser
    {
        try {
            $user = $this->unionCloud->getUserById($id);
            return DataUserModel::fromUnionCloudUser($user);
        } catch (ModelNotFoundException $e) {
            return new NullDataUserModel($id);
        }
    }

    /**
     * @inheritDoc
     */
    public function getWhere($attributes = []): \BristolSU\ControlDB\Contracts\Models\DataUser
    {
        if(isset($attributes['first_name'])) {
            $attributes['forename'] = $attributes['first_name'];
            unset($attributes['first_name']);
        }
        if(isset($attributes['last_name'])) {
            $attributes['surname'] = $attributes['last_name'];
            unset($attributes['last_name']);
        }
        return DataUserModel::fromUnionCloudUser(
            $this->unionCloud->searchForUser($attributes)
        );
    }

    /**
     * @inheritDoc
     * @throws PermissionDeniedException
     */
    public function create(?string $firstName = null, ?string $lastName = null, ?string $email = null, ?\DateTime $dob = null, ?string $preferredName = null): \BristolSU\ControlDB\Contracts\Models\DataUser
    {
        throw new PermissionDeniedException('UnionCloud does not allow user creation');
    }

    /**
     * Get all data users where the given attributes match, including additional attributes.
     *
     * @param array $attributes
     * @return Collection
     */
    public function getAllWhere($attributes = []): Collection
    {
        if(isset($attributes['first_name'])) {
            $attributes['forename'] = $attributes['first_name'];
            unset($attributes['first_name']);
        }
        if(isset($attributes['last_name'])) {
            $attributes['surname'] = $attributes['last_name'];
            unset($attributes['last_name']);
        }
        $users = collect();
        foreach($this->unionCloud->searchForUsers($attributes) as $user) {
            $users->push(DataUserModel::fromUnionCloudUser($user));
        }
        return $users;
    }

    public function update(int $id, ?string $firstName = null, ?string $lastName = null, ?string $email = null, ?\DateTime $dob = null, ?string $preferredName = null): \BristolSU\ControlDB\Contracts\Models\DataUser
    {
        throw new PermissionDeniedException('UnionCloud does not allow user updating');
    }
}
