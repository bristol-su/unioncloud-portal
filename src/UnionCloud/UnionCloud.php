<?php

namespace BristolSU\UnionCloud\UnionCloud;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Twigger\UnionCloud\API\Exception\Request\IncorrectRequestParameterException;
use Twigger\UnionCloud\API\Exception\Resource\ResourceNotFoundException;
use Twigger\UnionCloud\API\Resource\User;

class UnionCloud implements UnionCloudContract
{

    /**
     * @var \Twigger\UnionCloud\API\UnionCloud
     */
    private $unionCloud;

    public function __construct(\Twigger\UnionCloud\API\UnionCloud $unionCloud)
    {
        $this->unionCloud = $unionCloud;
    }

    /**
     * @inheritDoc
     */
    public function getUserGroupMemberships(int $userGroupId): Collection
    {
        try {
            return collect($this->unionCloud->userGroupMemberships()
                ->getByUserGroup($userGroupId, Carbon::now()->subSecond(), Carbon::now()->addSecond())
                ->get()->toArray());
        } catch (Exception $e) {
            $this->handleException($e, $userGroupId);
        }
    }

    protected function handleException(Exception $e, $id = null)
    {
        if ($e instanceof IncorrectRequestParameterException) {
            throw new ModelNotFoundException();
        }
        if ($e instanceof ResourceNotFoundException) {
            throw new ModelNotFoundException('The resource was not found: ' . $id);
        }
        if ($e->getPrevious() !== null) {
            throw $e->getPrevious();
        }
        throw $e;
    }

    /**
     * @inheritDoc
     */
    public function getUsersUserGroupMemberships(int $userId): Collection
    {
        try {
            return collect($this->unionCloud->userGroupMemberships()->getByUser($userId)->get()->toArray());
        } catch (Exception $e) {
            $this->handleException($e, $userId);
        }
    }

    public function getUserById(int $id): User
    {
        try {
            return $this->unionCloud->users()->setMode('standard')->getByUID($id)->get()->first();
        } catch (Exception $e) {
            $this->handleException($e, $id);
        }
    }

    public function searchForUser(array $attributes)
    {
        try {
            return $this->unionCloud->users()->setMode('standard')->search($attributes)->get()->first();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
}