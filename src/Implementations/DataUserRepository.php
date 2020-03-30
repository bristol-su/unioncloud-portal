<?php

namespace BristolSU\UnionCloud\Implementations;

use BristolSU\ControlDB\Contracts\Repositories\DataUser;
use BristolSU\UnionCloud\Models\DataUserModel;
use BristolSU\UnionCloud\Models\NullDataUserModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use BristolSU\UnionCloud\UnionCloud\UnionCloudContract as UnionCloud;

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
     */
    public function create(?string $firstName = null, ?string $lastName = null, ?string $email = null, ?\DateTime $dob = null, ?string $preferredName = null): \BristolSU\ControlDB\Contracts\Models\DataUser
    {
        throw new \Exception('UnionCloud does not allow user creation');
    }
}
