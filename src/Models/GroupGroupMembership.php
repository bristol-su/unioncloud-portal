<?php

namespace BristolSU\UnionCloud\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Twigger\UnionCloud\API\UnionCloud;

class GroupGroupMembership extends Model
{

    protected $table = 'unioncloud_groups_membership_usergroups';

    public static function userGroupsForGroup(int $groupId)
    {
        return static::query()->where('group_id', $groupId)->get()->pluck('usergroup_id');
    }

    public static function groupsFromUserGroup(int $userGroupId)
    {
        return static::query()->where('usergroup_id', $userGroupId)->get()->pluck('group_id');
    }

}
